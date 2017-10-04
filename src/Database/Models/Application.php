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
 * Application Class
 *
 * Represents a single user application for a specific year.
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://learn.userfrosting.com/database
 * @property int id
 * @property int user_id
 * @property int country_id
 * @property int expertise_id
 * @property int university_id
 * @property int year
 * @property date birthday
 * @property string phone
 * @property string street
 * @property int postal_code
 * @property string city
 * @property string state
 * @property string facebook
 * @property string twitter
 * @property string googleplus
 * @property string github
 * @property string xing
 * @property string linkedin
 * @property string referrer
 * @property string dietary_restrictions
 * @property string comment
 * @property string teammate1
 * @property string teammate2
 * @property string teammate3
 * @property boolean tos_accepted
 * @property boolean flag_accepted
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class Application extends Model
{
    /**
     * The name of the table for the current model.
     *
     * @var string
     */
    protected $table = 'applications';

    /**
     * The attributes.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'country_id',
        'expertise_id',
        'university_id',
        'year',
        'birthday',
        'phone',
        'street',
        'postal_code',
        'city',
        'state',
        'facebook',
        'twitter',
        'googleplus',
        'github',
        'xing',
        'linkedin',
        'referrer',
        'dietary_restrictions',
        'comment',
        'teammate1',
        'teammate2',
        'teammate3',
        'tos_accepted',
        'flag_accepted'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'tos_accepted' => 'bool',
        'flag_accepted' => 'bool'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Enable timestamps for applications.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Returns the user who created the application.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('user'), 'user_id');
    }

    /**
     * Returns the country.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('country'), 'country_id');
    }

    /**
     * Returns the expertise.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expertise()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('expertise'), 'expertise_id');
    }

    /**
     * Returns the university.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function university()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('university'), 'university_id');
    }

    /**
     * Determine if the property for this object exists.
     * We add relations here so that Twig will be able to find them.
     * See http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957
     * Every property in __get must also be implemented here for Twig to recognize it.
     *
     * @param string $name the name of the property to check.
     * @return bool true if the property is defined, false otherwise.
     */
    public function __isset($name)
    {
        if (in_array($name, [
                'display_name',
                'email',
            ])) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * Get a property for this object.
     *
     * @param string $name the name of the property to retrieve.
     * @throws \Exception the property does not exist for this object.
     * @return string the associated property.
     */
    public function __get($name)
    {
        if ($name === 'display_name') {
            return $this->user->getFullNameAttribute();
        } elseif ($name === 'email') {
            return $this->user->email;
        } else {
            return parent::__get($name);
        }
    }
}
