<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;

/**
 * General factory for the university model.
 */
$fm->define('UserFrosting\Sprinkle\Portal\Database\Models\University')->setDefinitions([
    'name' => Faker::unique()->sentence(1),
    'domain' => function() {
        return substr(uniqid(), -8) . '.' . substr(uniqid(), -2);
    }
]);
