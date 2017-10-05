<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;

/**
 * General factory for the application model.
 */
$fm->define('UserFrosting\Sprinkle\Portal\Database\Models\Application')->setDefinitions([
    'user_id' => 'factory|UserFrosting\Sprinkle\Account\Database\Models\User',
    'country_id' => 'factory|UserFrosting\Sprinkle\Portal\Database\Models\Country',
    'expertise_id' => 'factory|UserFrosting\Sprinkle\Portal\Database\Models\Expertise',
    'university_id' => 'factory|UserFrosting\Sprinkle\Portal\Database\Models\University',
    'year' => Faker::date('Y'),
    'birthday' => Faker::date('Y-m-d'),
    'street' => Faker::sentence(1),
    'postal_code' => Faker::randomNumber(5),
    'city' => Faker::sentence(1),
    'state' => Faker::sentence(1),
    'tos_accepted' => true,
    'flag_accepted' => true
]);
