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
 * Controller class for /admin/expertise/* URLs. Handles expertise-related activities, including creating and updating.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/routes-and-controllers
 */
class ExpertiseController extends SimpleController
{
    /**
     * Processes the request to create a expertise.
     *
     * Processes the request from the expertise creation form, checking that:
     * 1. The expertise name is not already in use;
     * 2. The user has permission to create a new expertise;
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
     * @see getModalCreateExpertise
     */
    public function create(Request $request, Response $response, array $args)
    {
        // Get POST parameter: name
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
        $schema = new RequestSchema('schema://requests/expertise.json');

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

        // Check if name already exists
        if ($classMapper->staticMethod('expertise', 'where', 'name', $data['name'])->first()) {
            $ms->addMessageTranslated('danger', 'EXPERTISE.NAME_IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($classMapper, $data, $ms, $currentUser) {
            // Create the expertise
            $expertise = $classMapper->createInstance('expertise', $data);

            // Store new expertise to database
            $expertise->save();

            $ms->addMessageTranslated('success', 'EXPERTISE.CREATION_SUCCESSFUL', $data);
        });

        return $response->withStatus(200);
    }

    /**
     * Processes the request to delete an existing expertise.
     *
     * Deletes the specified expertise.
     * Before doing so, checks that:
     * 1. The user has permission to delete this expertise;
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

        $expertise = ParamParser::getObjectById('expertise', $args);

        // If the expertise doesn't exist, return 404
        if (!$expertise) {
            throw new NotFoundException($request, $response);
        }

        $expertiseName = $expertise->name;

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($expertise, $expertiseName, $currentUser) {
            $expertise->delete();
            unset($expertise);

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} deleted expertise {$expertiseName}.", [
                'type' => 'expertise_delete',
                'user_id' => $currentUser->id
            ]);
        });

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'EXPERTISE.DELETION_SUCCESSFUL', [
            'name' => $expertiseName
        ]);

        return $response->withStatus(200);
    }


    /**
     * Renders the modal form for creating or updating a new expertise.
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

        // If we have an id key try to pre fill the form with expertise data
        $expertise = ($updateModal) ? ParamParser::getObjectById('expertise', $params) : null;

        /** @var \UserFrosting\I18n\MessageTranslator $translator */
        $translator = $this->ci->translator;

        // Create or update form actions
        if ($updateModal) {
            $form = [
                'action' => 'api/expertises/e/' . $params['id'],
                'method' => 'PUT',
                'submit_text' => $translator->translate('UPDATE')
            ];
        } else {
            $form = [
                'action' => 'api/expertises',
                'method' => 'POST',
                'submit_text' => $translator->translate('CREATE')
            ];
        }

        // Load validation rules
        $schema = new RequestSchema('schema://requests/expertise.json');
        $validator = new JqueryValidationAdapter($schema, $translator);

        return $this->ci->view->render($response, 'modals/expertise.html.twig', [
            'expertise' => $expertise,
            'form' => $form,
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form to confirm expertise deletion.
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

        $expertise = ParamParser::getObjectById('expertise', $params);

        // If the expertise doesn't exist, return 404
        if (!$expertise) {
            throw new NotFoundException($request, $response);
        }

        return $this->ci->view->render($response, 'modals/confirm-delete-expertise.html.twig', [
            'expertise' => $expertise,
            'form' => [
                'action' => "api/expertises/e/{$expertise->id}"
            ]
        ]);
    }

    /**
     * Returns a list of expertises
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

        $sprunje = $classMapper->createInstance('expertise_sprunje', $classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders the expertise listing page.
     *
     * This page renders a table of expertises.
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

        return $this->ci->view->render($response, 'pages/expertises.html.twig');
    }

    /**
     * Processes the request to update an existing expertise's details.
     *
     * Processes the request from the expertise update form, checking that:
     * 1. The expertise name is not already in use;
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

        $expertise = ParamParser::getObjectById('expertise', $args);

        // If the expertise doesn't exist, return 404
        if (!$expertise) {
            throw new NotFoundException($request, $response);
        }

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://requests/expertise.json');

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

        // Check if name already exists
        if (
            $data['name'] != $expertise->name &&
            $classMapper->staticMethod('expertise', 'where', 'name', $data['name'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'EXPERTISE.NAME_IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($data, $expertise, $currentUser) {
            foreach ($data as $name => $value) {
                if ($value !== $expertise->$name) {
                    $expertise->$name = $value;
                }
            }

            $expertise->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} updated details for expertise {$expertise->name}.", [
                'type' => 'expertise_update_info',
                'user_id' => $currentUser->id
            ]);
        });

        $ms->addMessageTranslated('success', 'EXPERTISE.UPDATED', [
            'name' => $expertise->name
        ]);

        return $response->withStatus(200);
    }
}
