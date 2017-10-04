<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\ServicesProvider;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

/**
 * Registers services for the portal sprinkle.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/services/the-di-container#service-providers
 */
class ServicesProvider
{
    /**
     * Register portal services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    {
        /**
         * Extend the 'classMapper' service to register model classes.
         *
         * Mappings added: Application, Country, Expertise, University
         */
        $container->extend('classMapper', function (ClassMapper $classMapper, $c) {
            $classMapper->setClassMapping('application', 'UserFrosting\Sprinkle\Portal\Database\Models\Application');
            $classMapper->setClassMapping('country', 'UserFrosting\Sprinkle\Portal\Database\Models\Country');
            $classMapper->setClassMapping('expertise', 'UserFrosting\Sprinkle\Portal\Database\Models\Expertise');
            $classMapper->setClassMapping('university', 'UserFrosting\Sprinkle\Portal\Database\Models\University');
            $classMapper->setClassMapping('application_sprunje', 'UserFrosting\Sprinkle\Portal\Sprunje\ApplicationSprunje');
            $classMapper->setClassMapping('country_sprunje', 'UserFrosting\Sprinkle\Portal\Sprunje\CountrySprunje');
            $classMapper->setClassMapping('expertise_sprunje', 'UserFrosting\Sprinkle\Portal\Sprunje\ExpertiseSprunje');
            $classMapper->setClassMapping('university_sprunje', 'UserFrosting\Sprinkle\Portal\Sprunje\UniversitySprunje');
            return $classMapper;
        });

        /**
         * Returns a callback that handles setting the `UF-Redirect` header after a successful login.
         */
        $container['redirect.onLogin'] = function (ContainerInterface $c) {
            /**
             * This method is invoked when a user completes the login process.
             *
             * @param Request $request The request object.
             * @param Response $response The response object.
             * @param array $args The passed arguments.
             * @return Response The response object.
             */
            return function (Request $request, Response $response, array $args) use ($c) {
                /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
                $authorizer = $c->authorizer;

                /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
                $currentUser = $c->authenticator->user();

                $redirect = $c->router->pathFor('index');
                if ($authorizer->checkAccess($currentUser, 'uri_dashboard')) {
                    $redirect = $c->router->pathFor('dashboard');
                }

                return $response->withHeader('UF-Redirect', $redirect);
            };
        };

        /**
         * Returns a callback that forwards to dashboard or index if user is already logged in.
         */
        $container['redirect.onAlreadyLoggedIn'] = function (ContainerInterface $c) {
            /**
             * This method is invoked when a user attempts to perform certain public actions when they are already logged in.
             *
             * @param Request $request The request object.
             * @param Response $response The response object.
             * @param array $args The passed arguments.
             * @return Response The response object.
             */
            return function (Request $request, Response $response, array $args) use ($c) {
                /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
                $authorizer = $c->authorizer;

                /** @var \UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
                $currentUser = $c->authenticator->user();

                $redirect = $c->router->pathFor('index');
                if ($authorizer->checkAccess($currentUser, 'uri_dashboard')) {
                    $redirect = $c->router->pathFor('dashboard');
                }

                return $response->withRedirect($redirect, 302);
            };
        };
    }
}
