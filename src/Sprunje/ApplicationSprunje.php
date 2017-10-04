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
 * Implements Sprunje for the applications API.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/database/data-sprunjing
 */
class ApplicationSprunje extends Sprunje
{
    /**
     * The name / url slug of the Spunje we are creating.
     *
     * @var string
     */
    protected $name = 'applications';

    /**
     * The columns that should be sortable.
     *
     * @var array
     */
    protected $sortable = [
        'street',
        'postal_code',
        'city',
        'state'
    ];

    /**
     * The columns that should be filterable.
     *
     * @var array
     */
    protected $filterable = [
        'street',
        'postal_code',
        'city',
        'state'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('application');

        return $query;
    }

    /**
    * {@inheritDoc}
    */
    protected function applyTransformations($collection)
    {
        // Replace id fields with values from results
        $collection->transform(function ($item, $key) {
            $item['display_name'] = $item->display_name;
            $item['email'] = $item->email;
            $item['country_name'] = $item->country->name;
            $item['expertise_name'] = $item->expertise->name;
            $item['university_name'] = $item->university->name;

            unset($item['user_id']);
            unset($item['country_id']);
            unset($item['expertise_id']);
            unset($item['university_id']);
            unset($item['user']);
            unset($item['country']);
            unset($item['expertise']);
            unset($item['university']);

            return $item;
        });

        return $collection;
    }
}
