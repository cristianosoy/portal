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
 * Expertise Class
 *
 * Represents a single expertise.
*
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/database
 * @property int id
 * @property string name
 */
class Expertise extends Model
{
    /**
     * The name of the table for the current model.
     *
     * @var string
     */
    protected $table = 'expertises';

    /**
     * The attributes.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];
}
