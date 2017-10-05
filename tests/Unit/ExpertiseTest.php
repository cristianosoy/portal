<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use UserFrosting\Sprinkle\Portal\Database\Models\Expertise;
use UserFrosting\Tests\TestCase;

/**
 * Expertise unit test class.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/advanced/automated-tests
 */
class ExpertiseTest extends TestCase
{
    /**
     * Test the creation of an expertise object and save it to the database.
     */
    public function testCreation()
    {
        /** @var \League\FactoryMuffin\FactoryMuffin $fm */
        $fm = $this->ci->factory;

        // Generate a test instance of the model
        $expertise = $fm->create('UserFrosting\Sprinkle\Portal\Database\Models\Expertise');
        $expertise->save();

        $this->assertInstanceOf(Expertise::class, $expertise);
    }
}
