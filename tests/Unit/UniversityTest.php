<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use UserFrosting\Sprinkle\Portal\Database\Models\University;
use UserFrosting\Tests\TestCase;

/**
 * University unit test class.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/advanced/automated-tests
 */
class UniversityTest extends TestCase
{
    /**
     * Test the creation of an university object and save it to the database.
     */
    public function testCreation()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $data = array();
        $data['name'] = 'Fake University';
        $data['domain'] = 'fake-university.com';
        $university = $classMapper->createInstance('university', $data);
        $university->save();

        $this->assertInstanceOf(University::class, $university);
    }

    /**
     * Test the email validation with a valid university email.
     *
     * @depends testCreation
     */
    public function testValidation()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $university = $classMapper->staticMethod('university', 'getUniversityByEmail', 'someone@fake-university.com');

        $this->assertInstanceOf(University::class, $university);
    }

    /**
     * Test the email validation with a invalid university email.
     *
     * @depends testCreation
     */
    public function testValidationFail()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $university = $classMapper->staticMethod('university', 'getUniversityByEmail', 'someone@fake-university.de');

        $this->assertNull($university);
    }

    /**
     * Create an university with data that are already in use.
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
        $data['name'] = 'Fake University';
        $data['domain'] = 'fake-university.com';
        $university = $classMapper->createInstance('university', $data);
        $university->save();
    }
}
