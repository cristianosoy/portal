<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Portal\Util\ParamParser;

/**
 * Controller class for /application/* URLs. Handles application-related activities, including creating and updating.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/routes-and-controllers
 */
class ApplicationController extends SimpleController
{
    /**
     * Create a new user application.
     *
     * Request type: POST
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     */
    public function create(Request $request, Response $response, array $args)
    {
        // Get POST parameters: name, slug, description
        $params = $request->getParsedBody();

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_account_settings')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Config\Config $config */
        $config = $this->ci->config;

        // Load the request schema
        $schema = new RequestSchema('schema://requests/application.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            return $response->withStatus(400);
        }

        // Check email against our known universities otherwise show a warning
        $university = $classMapper->staticMethod('university', 'getUniversityByEmail', $currentUser->email);
        if (!$university) {
            $ms->addMessageTranslated('danger', 'VALIDATE.UNI_EMAIL');
            return $response->withStatus(400);
        }

        // Check if applications are still open
        $now = Carbon::now();
        $untilDate = Carbon::parse($config['event.deadline']);
        // The second param indicates that we want the relative (negative) value if it is in the past
        $stillOpen = ($now->diffInDays($untilDate, false) >= 0);
        if (!$stillOpen) {
            $ms->addMessageTranslated('danger', 'APPLICATION.CLOSED');
            return $response->withStatus(400);
        }

        // Check if the selected country is valid otherwise show a warning
        $country = $classMapper->staticMethod('country', 'where', 'name', $data['country'])->first();
        if (!$country) {
            $ms->addMessageTranslated('danger', 'VALIDATE.COUNTRY');
            return $response->withStatus(400);
        }

        // Check if the selected expertise is valid otherwise show a warning
        $expertise = $classMapper->staticMethod('expertise', 'where', 'name', $data['expertise'])->first();
        if (!$expertise) {
            $ms->addMessageTranslated('danger', 'VALIDATE.EXPERTISE');
            return $response->withStatus(400);
        }

        // Check if user is at least or over 18 years old. This needs to be done after successful fortress validation!
        $birthday = Carbon::parse($data['birthday']);
        if ($birthday !== null && !($birthday->age >= 18)) {
            $ms->addMessageTranslated('danger', 'VALIDATE.AGE');
            return $response->withStatus(400);
        }

        // The given country was valid so we will fill in the country id
        $data['country_id'] = $country->id;

        // The given expertise was valid so we will fill in the expertise id
        $data['expertise_id'] = $expertise->id;

        // The user has a valid university email so we will fill in the university id
        $data['university_id'] = $university->id;

        // Load the users application if it exists
        $application = $classMapper->staticMethod('application', 'where', 'user_id', $currentUser->id)->first();

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($currentUser, $classMapper, $ms, $config, $application, $data) {
            // Application created / updated string for user message / log
            $action = ($application) ? 'updated' : 'created';

            if ($application) {
                $application->update($data);
            } else {
                // For new applications we need to manually set the userId and the current year
                $data['user_id'] = $currentUser->id;
                $data['year'] = $config['event.year'];

                $application = $classMapper->createInstance('application', $data);
                $application->save();

                // Create and send application email
                $message = new TwigMailMessage($this->ci->view, 'mail/application-create.html.twig');

                $message->from($config['address_book.admin'])
                    ->addEmailRecipient(new EmailRecipient($currentUser->email, $currentUser->full_name))
                    ->addParams([
                        'user' => $currentUser
                    ]);

                $this->ci->mailer->send($message);
            }

            // Create activity record
            $this->ci->userActivityLogger->info('User ' . $currentUser->user_name . ' ' . $action . ' their application.', [
                'type' => 'application_' . $action
            ]);

            // Application saved successfully
            $ms->addMessageTranslated('success', 'APPLICATION.' . strtoupper($action));
        });

        return $response->withStatus(200);
    }

    /**
     * Form to view the users application.
     *
     * Request type: GET
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     */
    public function view(Request $request, Response $response, array $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var \UserFrosting\Sprinkle\Core\Router $router */
        $router = $this->ci->router;

        // Redirect to login if not logged in
        if (!$currentUser) {
            return $response->withRedirect($router->pathFor('login'));
        }

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_account_settings')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Config\Config $config */
        $config = $this->ci->config;

        /** @var \Illuminate\Contracts\Cache\Repository $cache */
        $cache = $this->ci->cache;

        // Get a list of all locales
        $locales = $config['site.locales.available'];

        // Load the request schema
        $schema = new RequestSchema('schema://requests/application.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        // Check email against our known universities otherwise show a warning
        $university = $classMapper->staticMethod('university', 'getUniversityByEmail', $currentUser->email);
        if (!$university) {
            $ms->addMessageTranslated('danger', 'VALIDATE.UNI_EMAIL');
        }

        // Check if applications are still open
        $now = Carbon::now();
        $untilDate = Carbon::parse($config['event.deadline']);
        // The second param indicates that we want the relative (negative) value if it is in the past
        $stillOpen = ($now->diffInDays($untilDate, false) >= 0);
        if (!$stillOpen) {
            $ms->addMessageTranslated('danger', 'APPLICATION.CLOSED');
        }

        // Load the users application if it exists
        $application = $classMapper->staticMethod('application', 'where', 'user_id', $currentUser->id)->first();

        // Load all countries and cache them for one day
        $countries = $cache->remember('countries', 1440, function () use ($classMapper) {
            return $classMapper->staticMethod('country', 'get');
        });

        // Load all expertises and cache them for one day
        $expertises = $cache->remember('expertises', 1440, function () use ($classMapper) {
            return $classMapper->staticMethod('expertise', 'get');
        });

        return $this->ci->view->render($response, 'pages/application.html.twig', [
            'user' => $currentUser,
            'application' => $application,
            'countries' => $countries,
            'expertises' => $expertises,
            'locales' => $locales,
            'form' => [
                'action' => '',
                'method' => 'POST'
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Processes the request to delete the application.
     *
     * Request type: DELETE
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function delete(Request $request, Response $response, array $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_account_settings')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        $application = ParamParser::getObjectById('application', $args);

        // If the application doesn't exist or the user doesn't have enough rights to delete it, return 404
        if (
            !$application ||
            ($application->user_id !== $currentUser->id && !$authorizer->checkAccess($currentUser, 'uri_users'))
        ) {
            throw new NotFoundException($request, $response);
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($currentUser, $application) {
            $user = $application->user;
            $application->delete();
            unset($application);

            // Create activity record
            $this->ci->userActivityLogger->info('Application from ' . $user->user_name . ' was deleted.', [
                'type' => 'application_deleted'
            ]);
        });

        $ms->addMessageTranslated('success', 'APPLICATION.DELETED');

        return $response->withStatus(200);
    }

    /**
     * Returns a list of applications
     *
     * Generates a list of applications, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     *
     * Request type: GET
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     */
    public function getList(Request $request, Response $response, array $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = $classMapper->createInstance('application_sprunje', $classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders the university listing page.
     *
     * This page renders a table of universities.
     * This page requires authentication.
     *
     * Request type: GET
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     */
    public function pageList(Request $request, Response $response, array $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'pages/applications.html.twig');
    }

    /**
     * Renders the modal form for viewing a user application.
     *
     * This does NOT render a complete page. Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     *
     * Request type: GET
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function getModalView(Request $request, Response $response, array $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        $application = ParamParser::getObjectById('application', $args);

        // If the application doesn't exist, return 404
        if (!$application) {
            throw new NotFoundException($request, $response);
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \Illuminate\Contracts\Cache\Repository $cache */
        $cache = $this->ci->cache;

        /** @var \UserFrosting\I18n\MessageTranslator $translator */
        $translator = $this->ci->translator;

        // Load validation rules
        $schema = new RequestSchema('schema://requests/application.json');
        $validator = new JqueryValidationAdapter($schema, $translator);

        $form = [
            'action' => 'modals/applications/view/' . $args['id'],
            'method' => 'get',
            'submit_text' => $translator->translate('VIEW')
        ];

        // Load all countries and cache them for one day
        $countries = $cache->remember('countries', 1440, function () use ($classMapper) {
            return $classMapper->staticMethod('country', 'get');
        });

        // Load all expertises and cache them for one day
        $expertises = $cache->remember('expertises', 1440, function () use ($classMapper) {
            return $classMapper->staticMethod('expertise', 'get');
        });

        return $this->ci->view->render($response, 'modals/application.html.twig', [
            'readonly' => 'readonly',
            'user' => $application->user,
            'application' => $application,
            'countries' => $countries,
            'expertises' => $expertises,
            'form' => $form,
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form to confirm application deletion.
     *
     * This does NOT render a complete page. Instead, it renders the HTML for the form, which can be embedded in other pages.
     *
     * Request type: GET
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function getModalConfirmDelete(Request $request, Response $response, array $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_account_settings')) {
            throw new ForbiddenException();
        }

        $application = ParamParser::getObjectById('application', $params);

        // If the application doesn't exist or the user doesn't have enough rights to delete it, return 404
        if (
            !$application ||
            ($application->user_id !== $currentUser->id && !$authorizer->checkAccess($currentUser, 'uri_users'))
        ) {
            throw new NotFoundException($request, $response);
        }

        return $this->ci->view->render($response, 'modals/confirm-delete-application.html.twig', [
            'application' => $application,
            'form' => [
                'action' => "api/applications/a/{$application->id}"
            ]
        ]);
    }

    /**
     * Returns the modal containing application terms of service.
     *
     * This does NOT render a complete page. Instead, it renders the HTML for the form, which can be embedded in other pages.
     *
     * Request type: GET
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     */
    public function getModalApplicationTos(Request $request, Response $response, array $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_account_settings')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Config\Config $config */
        $config = $this->ci->config;

        // Get event year
        $year = $config['event.year'];

        return $this->ci->view->render($response, 'modals/application-tos.html.twig', [
            'year' => $year
        ]);
    }

    /**
     * Processes the request to accept an existing application's.
     *
     * Processes the request from the application update form, checking that:
     * 1. The application exists;
     * 2. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     *
     * Request type: PUT
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updateInfo(Request $request, Response $response, array $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        $application = ParamParser::getObjectById('application', $args);

        // If the application doesn't exist, return 404
        if (!$application) {
            throw new NotFoundException($request, $response);
        }

        /** @var \UserFrosting\Config\Config $config */
        $config = $this->ci->config;

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($application, $config) {
            $application->flag_accepted ^= 1;
            $application->save();

            if ($application->flag_accepted) {
                // Create and send application accepted email
                $message = new TwigMailMessage($this->ci->view, 'mail/application-accept.html.twig');

                $message->from($config['address_book.admin'])
                    ->addEmailRecipient(new EmailRecipient($application->user->email, $application->user->full_name))
                    ->addParams([
                        'user' => $application->user
                    ]);

                $this->ci->mailer->send($message);
            }

            // Create activity record
            $this->ci->userActivityLogger->info("Application from {$application->user->user_name} got accepted.", [
                'type' => 'application_accepted',
                'user_id' => $application->user->id
            ]);
        });

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'APPLICATION.UPDATED', [
            'name' => $application->display_name
        ]);

        return $response->withStatus(200);
    }
}
