<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Controller;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Portal\Util\ParamParser;
use UserFrosting\Support\Exception\ForbiddenException;

/**
 * Controller class for /admin/university/* URLs. Handles university-related activities, including creating and updating.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/routes-and-controllers
 */
class UniversityController extends SimpleController
{
    /**
     * Processes the request to create a university.
     *
     * Processes the request from the country creation form, checking that:
     * 1. The university name is not already in use;
     * 2. The user has permission to create a new university;
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     *
     * Request type: POST
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws ForbiddenException
     * @see getModalCreateUniversity
     */
    public function create(Request $request, Response $response, array $args)
    {
        // Get POST parameters: name, domain
        $params = $request->getParsedBody();

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://requests//university.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Check if name or domain already exist
        if ($classMapper->staticMethod('university', 'where', 'name', $data['name'])->first()) {
            $ms->addMessageTranslated('danger', 'UNIVERSITY.NAME_IN_USE', $data);
            $error = true;
        }

        if ($classMapper->staticMethod('university', 'where', 'domain', $data['domain'])->first()) {
            $ms->addMessageTranslated('danger', 'UNIVERSITY.DOMAIN_IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($classMapper, $data, $ms) {
            // Create the university
            $university = $classMapper->createInstance('university', $data);

            // Store new university to database
            $university->save();

            $ms->addMessageTranslated('success', 'UNIVERSITY.CREATION_SUCCESSFUL', $data);
        });

        return $response->withStatus(200);
    }

    /**
     * Processes the request to delete an existing university.
     *
     * Deletes the specified university.
     * Before doing so, checks that:
     * 1. The user has permission to delete this university;
     * 2. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
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
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        $university = ParamParser::getObjectById('university', $args);

        // If the university doesn't exist, return 404
        if (!$university) {
            throw new NotFoundException($request, $response);
        }

        $universityName = $university->name;

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($university, $universityName, $currentUser) {
            $university->delete();
            unset($university);

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} deleted university {$universityName}.", [
                'type' => 'university_delete',
                'user_id' => $currentUser->id
            ]);
        });

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'UNIVERSITY.DELETION_SUCCESSFUL', [
            'name' => $universityName
        ]);

        return $response->withStatus(200);
    }

    /**
     * Renders the modal form for creating or updating a new university.
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
     */
    public function getModalCreateOrEdit(Request $request, Response $response, array $args)
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

        // Create or update modal?
        $updateModal = array_key_exists('id', $params);

        // If we have an id key try to pre fill the form with university data
        $university = ($updateModal) ? ParamParser::getObjectById('university', $params) : null;

        /** @var \UserFrosting\I18n\MessageTranslator $translator */
        $translator = $this->ci->translator;

        // Create or update form actions
        if ($updateModal) {
            $form = [
                'action' => 'api/universities/u/' . $params['id'],
                'method' => 'PUT',
                'submit_text' => $translator->translate('UPDATE')
            ];
        } else {
            $form = [
                'action' => 'api/universities',
                'method' => 'POST',
                'submit_text' => $translator->translate('CREATE')
            ];
        }

        // Load validation rules
        $schema = new RequestSchema('schema://requests//university.json');
        $validator = new JqueryValidationAdapter($schema, $translator);

        return $this->ci->view->render($response, 'modals/university.html.twig', [
            'university' => $university,
            'form' => $form,
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form to confirm university deletion.
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
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        $university = ParamParser::getObjectById('university', $params);

        // If the university doesn't exist, return 404
        if (!$university) {
            throw new NotFoundException($request, $response);
        }

        return $this->ci->view->render($response, 'modals/confirm-delete-university.html.twig', [
            'university' => $university,
            'form' => [
                'action' => "api/universities/u/{$university->id}"
            ]
        ]);
    }

    /**
     * Returns a list of universities
     *
     * Generates a list of universities, optionally paginated, sorted and/or filtered.
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

        $sprunje = $classMapper->createInstance('university_sprunje', $classMapper, $params);

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

        return $this->ci->view->render($response, 'pages/universities.html.twig');
    }

    /**
     * Processes the request to update an existing university's details.
     *
     * Processes the request from the role update form, checking that:
     * 1. The university name/domain are not already in use;
     * 2. The user has the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
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
     * @see getModalCreateOrEdit
     */
    public function updateInfo(Request $request, Response $response, array $args)
    {
        // Get PUT parameters: (name, slug, description)
        $params = $request->getParsedBody();

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        $university = ParamParser::getObjectById('university', $args);

        // If the university doesn't exist, return 404
        if (!$university) {
            throw new NotFoundException($request, $response);
        }

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://requests//university.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Check if name or domain already exist
        if (
            $data['name'] != $university->name &&
            $classMapper->staticMethod('university', 'where', 'name', $data['name'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'UNIVERSITY.NAME_IN_USE', $data);
            $error = true;
        }

        if (
            $data['domain'] != $university->domain &&
            $classMapper->staticMethod('university', 'where', 'domain', $data['domain'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'UNIVERSITY.DOMAIN_IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($data, $university, $currentUser) {
            foreach ($data as $name => $value) {
                if ($value !== $university->$name) {
                    $university->$name = $value;
                }
            }

            $university->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} updated details for university {$university->name}.", [
                'type' => 'university_update_info',
                'user_id' => $currentUser->id
            ]);
        });

        $ms->addMessageTranslated('success', 'UNIVERSITY.UPDATED', [
            'name' => $university->name
        ]);

        return $response->withStatus(200);
    }
}
