<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Database\Migrations\v010;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\System\Bakery\Migration;

/**
 * Universities table migration
 *
 * Version 0.1.0
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://laravel.com/docs/5.4/migrations#tables
 */
class UniversitiesTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('universities')) {
            $this->schema->create('universities', function (Blueprint $table) {
                $table->increments('id');
                $table->string('domain')->nullable(false)->unique();
                $table->string('name')->nullable(false);
                $table->boolean('imported')->nullable(false)->default(false);

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('universities');
    }
}
