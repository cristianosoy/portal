<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;

/**
 * General factory for the country model.
 */
$fm->define('UserFrosting\Sprinkle\Portal\Database\Models\Country')->setDefinitions([
    'name' => Faker::unique()->sentence(1),
    'code' => function() {
        return strtoupper(substr(uniqid('', true), -4));
    }
]);
