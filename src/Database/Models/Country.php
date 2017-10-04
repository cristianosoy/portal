<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Country Class
 *
 * Represents a single country.
*
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/database
 * @property int id
 * @property string name
 * @property string code
 */
class Country extends Model
{
    /**
     * The name of the table for the current model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The attributes.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code'
    ];
}
