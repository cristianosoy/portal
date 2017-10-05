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
use UserFrosting\Tests\DatabaseTransactions;

/**
 * Expertise unit test class.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/advanced/automated-tests
 */
class ExpertiseTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test the creation of an expertise object and save it to the database.
     */
    public function testCreation()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $data = array();
        $data['name'] = 'Testing';
        $expertise = $classMapper->createInstance('expertise', $data);
        $expertise->save();

        $this->assertInstanceOf(Expertise::class, $expertise);
    }
}
