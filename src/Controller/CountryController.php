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
 * Controller class for /admin/country/* URLs. Handles country-related activities, including creating and updating.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/routes-and-controllers
 */
class CountryController extends SimpleController
{
    /**
     * Processes the request to create a country.
     *
     * Processes the request from the country creation form, checking that:
     * 1. The country code is not already in use;
     * 2. The user has permission to create a new country;
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
     * @see getModalCreateCountry
     */
    public function create(Request $request, Response $response, array $args)
    {
        // Get POST parameters: name, code
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
        $schema = new RequestSchema('schema://requests/country.json');

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

        // Check if name or code already exist
        if ($classMapper->staticMethod('country', 'where', 'name', $data['name'])->first()) {
            $ms->addMessageTranslated('danger', 'COUNTRY.NAME_IN_USE', $data);
            $error = true;
        }

        if ($classMapper->staticMethod('country', 'where', 'code', $data['code'])->first()) {
            $ms->addMessageTranslated('danger', 'COUNTRY.CODE_IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($classMapper, $data, $ms) {
            // Create the country
            $country = $classMapper->createInstance('country', $data);

            // Store new country to database
            $country->save();

            $ms->addMessageTranslated('success', 'COUNTRY.CREATION_SUCCESSFUL', $data);
        });

        return $response->withStatus(200);
    }

    /**
     * Processes the request to delete an existing country.
     *
     * Deletes the specified country.
     * Before doing so, checks that:
     * 1. The user has permission to delete this country;
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

        $country = ParamParser::getObjectById('country', $args);

        // If the country doesn't exist, return 404
        if (!$country) {
            throw new NotFoundException($request, $response);
        }

        $countryName = $country->name;

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($country, $countryName, $currentUser) {
            $country->delete();
            unset($country);

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} deleted country {$countryName}.", [
                'type' => 'country_delete',
                'user_id' => $currentUser->id
            ]);
        });

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'COUNTRY.DELETION_SUCCESSFUL', [
            'name' => $countryName
        ]);

        return $response->withStatus(200);
    }

    /**
     * Renders the modal form for creating or updating a new country.
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

        // If we have an id key try to pre fill the form with country data
        $country = ($updateModal) ? ParamParser::getObjectById('country', $params) : null;

        /** @var \UserFrosting\I18n\MessageTranslator $translator */
        $translator = $this->ci->translator;

        // Create or update form actions
        if ($updateModal) {
            $form = [
                'action' => 'api/countries/c/' . $params['id'],
                'method' => 'PUT',
                'submit_text' => $translator->translate('UPDATE')
            ];
        } else {
            $form = [
                'action' => 'api/countries',
                'method' => 'POST',
                'submit_text' => $translator->translate('CREATE')
            ];
        }

        // Load validation rules
        $schema = new RequestSchema('schema://requests/country.json');
        $validator = new JqueryValidationAdapter($schema, $translator);

        return $this->ci->view->render($response, 'modals/country.html.twig', [
            'country' => $country,
            'form' => $form,
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form to confirm country deletion.
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

        $country = ParamParser::getObjectById('country', $params);

        // If the country doesn't exist, return 404
        if (!$country) {
            throw new NotFoundException($request, $response);
        }

        return $this->ci->view->render($response, 'modals/confirm-delete-country.html.twig', [
            'country' => $country,
            'form' => [
                'action' => "api/countries/c/{$country->id}"
            ]
        ]);
    }

    /**
     * Returns a list of countries
     *
     * Generates a list of countries, optionally paginated, sorted and/or filtered.
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

        $sprunje = $classMapper->createInstance('country_sprunje', $classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders the country listing page.
     *
     * This page renders a table of countries.
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

        return $this->ci->view->render($response, 'pages/countries.html.twig');
    }

    /**
     * Processes the request to update an existing country's details.
     *
     * Processes the request from the role update form, checking that:
     * 1. The country name/code are not already in use;
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

        $country = ParamParser::getObjectById('country', $args);

        // If the country doesn't exist, return 404
        if (!$country) {
            throw new NotFoundException($request, $response);
        }

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://requests/country.json');

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

        // Check if name or code already exist
        if (
            $data['name'] != $country->name &&
            $classMapper->staticMethod('country', 'where', 'name', $data['name'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'COUNTRY.NAME_IN_USE', $data);
            $error = true;
        }

        if (
            $data['code'] != $country->code &&
            $classMapper->staticMethod('country', 'where', 'code', $data['code'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'COUNTRY.CODE_IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($data, $country, $currentUser) {
            foreach ($data as $name => $value) {
                if ($value !== $country->$name) {
                    $country->$name = $value;
                }
            }

            $country->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} updated details for country {$country->name}.", [
                'type' => 'country_update_info',
                'user_id' => $currentUser->id
            ]);
        });

        $ms->addMessageTranslated('success', 'COUNTRY.UPDATED', [
            'name' => $country->name
        ]);

        return $response->withStatus(200);
    }
}
