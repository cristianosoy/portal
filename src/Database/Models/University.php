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
 * University Class
 *
 * Represents a single university to validate user email addresses.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/database
 * @property int id
 * @property string name
 * @property string domain
 * @property boolean imported
 */
class University extends Model
{
    /**
     * The name of the table for the current model.
     *
     * @var string
     */
    protected $table = 'universities';

    /**
     * The attributes.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'domain',
        'imported'
    ];

    /**
     * Get university name by email.
     *
     * @param string $email The user email.
     * @return University|null The university object if it exists otherwise null.
     */
    public static function getUniversityByEmail($email)
    {
        $emailParts = explode('.', $email);
        $emailTld = end($emailParts);

        // Get all valid domain names for the tld so we do not have to check all domains
        $universitiesByTld = static::where('domain', 'LIKE', '%.' . $emailTld)->get();

        foreach ($universitiesByTld as $university) {
            if (ends_with($email, $university->domain)) {
                return $university;
            }
        }

        return null;
    }
}
