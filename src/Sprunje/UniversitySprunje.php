<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * Implements Sprunje for the universities API.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/database/data-sprunjing
 */
class UniversitySprunje extends Sprunje
{
    /**
     * The name / url slug of the Spunje we are creating.
     *
     * @var string
     */
    protected $name = 'universities';

    /**
     * The columns that should be sortable.
     *
     * @var array
     */
    protected $sortable = [
        'name',
        'domain',
        'imported'
    ];

    /**
     * The columns that should be filterable.
     *
     * @var array
     */
    protected $filterable = [
        'name',
        'domain',
        'imported'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('university');

        return $query;
    }
}
