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
 * Implements Sprunje for the expertises API.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/database/data-sprunjing
 */
class ExpertiseSprunje extends Sprunje
{
    /**
     * The name / url slug of the Spunje we are creating.
     *
     * @var string
     */
    protected $name = 'expertises';

    /**
     * The columns that should be sortable.
     *
     * @var array
     */
    protected $sortable = [
        'name'
    ];

    /**
     * The columns that should be filterable.
     *
     * @var array
     */
    protected $filterable = [
        'name'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('expertise');

        return $query;
    }
}
