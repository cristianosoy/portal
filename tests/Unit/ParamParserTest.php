<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use UserFrosting\Sprinkle\Portal\Database\Models\University;
use UserFrosting\Sprinkle\Portal\Util\ParamParser;
use UserFrosting\Tests\TestCase;
use UserFrosting\Tests\DatabaseTransactions;
use UserFrosting\Support\Exception\BadRequestException;

/**
 * Param parser unit test class.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/advanced/automated-tests
 */
class ParamParserTest extends TestCase
{
    /**
     * Test the ParamParser class with valid parameters.
     */
    public function testParamParser()
    {
        $params = array();
        $params['id'] = 1;
        $university = ParamParser::getObjectById('university', $params);

        $this->assertInstanceOf(University::class, $university);
    }

    /**
     * Test the ParamParser class with invalid parameters.
     */
    public function testParamParserFail()
    {
        // We Expect a BadRequestException here
        $this->expectException(BadRequestException::class);

        $params = array();
        ParamParser::getObjectById('university', $params);
    }
}