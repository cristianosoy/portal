<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use UserFrosting\Sprinkle\Portal\Database\Models\Application;
use UserFrosting\Sprinkle\Portal\Database\Models\Country;
use UserFrosting\Sprinkle\Portal\Database\Models\Expertise;
use UserFrosting\Sprinkle\Portal\Database\Models\University;
use UserFrosting\Tests\TestCase;

/**
 * Application unit test class.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/advanced/automated-tests
 */
class ApplicationTest extends TestCase
{
    /**
     * Test the creation of an application object and save it to the database.
     */
    public function testCreation()
    {
        /** @var \League\FactoryMuffin\FactoryMuffin $fm */
        $fm = $this->ci->factory;

        // Generate a test instance of the model
        $application = $fm->create('UserFrosting\Sprinkle\Portal\Database\Models\Application');
        $application->user->save();
        $application->country->save();
        $application->expertise->save();
        $application->university->save();
        $application->save();

        // Check if expected objects match
        $this->assertInstanceOf(Application::class, $application);
        $this->assertInstanceOf(Country::class, $application->country);
        $this->assertInstanceOf(Expertise::class, $application->expertise);
        $this->assertInstanceOf(University::class, $application->university);
    }
}
