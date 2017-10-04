<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Sprinkle\Account\Controller\Exception\SpammyRequestException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;

/**
 * Controller class for /account/register URL. Handles the account registration.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/routes-and-controllers
 */
class AccountController extends SimpleController
{
    /**
     * Check registration email and forward the request to the uf account controller.
     *
     * Request type: POST
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The passed arguments.
     * @return Response The response object.
     * @throws SpammyRequestException
     */
    public function register(Request $request, Response $response, array $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Get POST parameters: email
        $params = $request->getParsedBody();

        // Check the honeypot. 'spiderbro' is not a real field, it is hidden on the main page and must be submitted with its default value for this to be processed.
        if (!isset($params['spiderbro']) || $params['spiderbro'] !== 'http://') {
            throw new SpammyRequestException('Possible spam received: ' . print_r($params, true));
        }

        // Load the request schema
        $schema = new RequestSchema('schema://requests/email.json');

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
        $university = $classMapper->staticMethod('university', 'getUniversityByEmail', $data['email']);
        if (!$university) {
            $ms->addMessageTranslated('danger', 'VALIDATE.UNI_EMAIL');
            return $response->withStatus(400);
        }

        $controller = new \UserFrosting\Sprinkle\Account\Controller\AccountController($this->ci);
        return $controller->register($request, $response, $args);
    }
}
