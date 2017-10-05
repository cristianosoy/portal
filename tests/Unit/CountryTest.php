<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use UserFrosting\Sprinkle\Portal\Database\Models\Country;
use UserFrosting\Tests\TestCase;

/**
 * Country unit test class.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/advanced/automated-tests
 */
class CountryTest extends TestCase
{
    /**
     * Test the creation of a country object and save it to the database.
     */
    public function testCreation()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $data = array();
        $data['name'] = 'Test';
        $data['code'] = 'TEST';
        $country = $classMapper->createInstance('country', $data);
        $country->save();

        $this->assertInstanceOf(Country::class, $country);
    }

    /**
     * Create an country with data that are already in use.
     *
     * @depends testCreation
     */
    public function testUnique()
    {
        // We Expect a PDOException here
        $this->expectException(\PDOException::class);

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $data = array();
        $data['name'] = 'Test';
        $data['code'] = 'TEST';
        $country = $classMapper->createInstance('country', $data);
        $country->save();
    }
}
