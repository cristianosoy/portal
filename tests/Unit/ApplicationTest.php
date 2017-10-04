<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use UserFrosting\Sprinkle\Portal\Database\Models\Application;
use UserFrosting\Tests\TestCase;
use UserFrosting\Tests\DatabaseTransactions;

/**
 * Application unit test class.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/advanced/automated-tests
 */
class ApplicationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test the creation of an application object and save it to the database.
     */
    public function testCreation()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $data = array();
        $data['user_name'] = 'test.test';
        $data['email'] = 'test.test@the-fake-university.com';
        $data['first_name'] = 'Test';
        $data['last_name'] = 'Test';
        $data['password'] = 'test.test123!';
        $user = $classMapper->createInstance('user', $data);
        $user->save();

        $data = array();
        $data['name'] = 'TEST1';
        $data['code'] = 'TEST1';

        $country = $classMapper->createInstance('country', $data);
        $country->save();

        $data = array();
        $data['name'] = 'Throwing exceptions';

        $expertise = $classMapper->createInstance('expertise', $data);
        $expertise->save();

        $data = array();
        $data['name'] = 'The Fake University';
        $data['domain'] = 'the-fake-university.com';

        $university = $classMapper->createInstance('university', $data);
        $university->save();

        $data = array();
        $data['country_id'] = $country->id;
        $data['expertise_id'] = $expertise->id;
        $data['university_id'] = $university->id;
        $data['user_id'] = $user->id;
        $data['year'] = 2017;
        $data['birthday'] = '1990-10-10';
        $data['street'] = 'Test Str. 123';
        $data['postal_code'] = 12345;
        $data['city'] = 'Test';
        $data['state'] = 'NRW';
        $data['tos_accepted'] = true;
        $data['flag_accepted'] = true;

        $application = $classMapper->createInstance('application', $data);
        $application->save();

        $this->assertInstanceOf(Application::class, $application);
    }
}
