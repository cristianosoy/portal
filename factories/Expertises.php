<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;

/**
 * General factory for the expertise model.
 */
$fm->define('UserFrosting\Sprinkle\Portal\Database\Models\Expertise')->setDefinitions([
    'name' => Faker::sentence(2)
]);
