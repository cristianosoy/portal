<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;

/** @var \Slim\App $app */
global $app;

$app->post('/account/register', 'UserFrosting\Sprinkle\Portal\Controller\AccountController:register');

$app->get('/', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:view')
    ->setName('index')
    ->add('checkEnvironment');

$app->group('/', function () {
    $this->delete('', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:delete');

    $this->post('', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:create');
})->add('authGuard');

$app->group('/api', function () {
    $this->group('/applications', function () {
        $this->get('', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:getList');

        $this->put('/a/{id}', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:updateInfo');

        $this->delete('/a/{id}', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:delete');
    });

    $this->group('/countries', function () {
        $this->get('', 'UserFrosting\Sprinkle\Portal\Controller\CountryController:getList');

        $this->post('', 'UserFrosting\Sprinkle\Portal\Controller\CountryController:create');

        $this->put('/c/{id}', 'UserFrosting\Sprinkle\Portal\Controller\CountryController:updateInfo');

        $this->delete('/c/{id}', 'UserFrosting\Sprinkle\Portal\Controller\CountryController:delete');
    });

    $this->group('/expertises', function () {
        $this->get('', 'UserFrosting\Sprinkle\Portal\Controller\ExpertiseController:getList');

        $this->post('', 'UserFrosting\Sprinkle\Portal\Controller\ExpertiseController:create');

        $this->put('/e/{id}', 'UserFrosting\Sprinkle\Portal\Controller\ExpertiseController:updateInfo');

        $this->delete('/e/{id}', 'UserFrosting\Sprinkle\Portal\Controller\ExpertiseController:delete');
    });

    $this->group('/universities', function () {
        $this->get('', 'UserFrosting\Sprinkle\Portal\Controller\UniversityController:getList');

        $this->post('', 'UserFrosting\Sprinkle\Portal\Controller\UniversityController:create');

        $this->put('/u/{id}', 'UserFrosting\Sprinkle\Portal\Controller\UniversityController:updateInfo');

        $this->delete('/u/{id}', 'UserFrosting\Sprinkle\Portal\Controller\UniversityController:delete');
    });
})->add('authGuard');

$app->group('/admin', function () {
    $this->get('/applications', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:pageList')
        ->setName('uri_applications');

    $this->get('/countries', 'UserFrosting\Sprinkle\Portal\Controller\CountryController:pageList')
        ->setName('uri_countries');

    $this->get('/expertises', 'UserFrosting\Sprinkle\Portal\Controller\ExpertiseController:pageList')
        ->setName('uri_expertises');

    $this->get('/universities', 'UserFrosting\Sprinkle\Portal\Controller\UniversityController:pageList')
        ->setName('uri_universities');
})->add('authGuard');

$app->group('/modals', function () {
    $this->group('/applications', function () {
        $this->get('/view/{id}', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:getModalView');

        $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:getModalConfirmDelete');

        $this->get('/tos', 'UserFrosting\Sprinkle\Portal\Controller\ApplicationController:getModalApplicationTos');
    });

    $this->group('/countries', function () {
        $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Portal\Controller\CountryController:getModalConfirmDelete');

        $this->get('/create', 'UserFrosting\Sprinkle\Portal\Controller\CountryController:getModalCreateOrEdit');

        $this->get('/edit', 'UserFrosting\Sprinkle\Portal\Controller\CountryController:getModalCreateOrEdit');
    });

    $this->group('/expertises', function () {
        $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Portal\Controller\ExpertiseController:getModalConfirmDelete');

        $this->get('/create', 'UserFrosting\Sprinkle\Portal\Controller\ExpertiseController:getModalCreateOrEdit');

        $this->get('/edit', 'UserFrosting\Sprinkle\Portal\Controller\ExpertiseController:getModalCreateOrEdit');
    });

    $this->group('/universities', function () {
        $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Portal\Controller\UniversityController:getModalConfirmDelete');

        $this->get('/create', 'UserFrosting\Sprinkle\Portal\Controller\UniversityController:getModalCreateOrEdit');

        $this->get('/edit', 'UserFrosting\Sprinkle\Portal\Controller\UniversityController:getModalCreateOrEdit');
    });
})->add('authGuard');
