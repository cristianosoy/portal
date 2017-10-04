<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Database\Migrations\v010;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Portal\Database\Models\Expertise;
use UserFrosting\System\Bakery\Migration;

/**
 * Expertises table migration
 *
 * Version 0.1.0
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://laravel.com/docs/5.4/migrations#tables
 */
class ExpertisesTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('expertises')) {
            $this->schema->create('expertises', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable(false);

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });

            $expertises = [
                new Expertise(['name' => 'Business']),
                new Expertise(['name' => 'Design']),
                new Expertise(['name' => 'Development']),
                new Expertise(['name' => 'Other'])
            ];

            foreach ($expertises as $expertise) {
                $expertise->save();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('expertises');
    }
}
