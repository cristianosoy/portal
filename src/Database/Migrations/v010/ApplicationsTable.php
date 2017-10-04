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
 * Applications table migration
 *
 * Version 0.1.0
 *
 * @author Kai Schröer (https://schroeer.co)
 * @see https://laravel.com/docs/5.4/migrations#tables
 */
class ApplicationsTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Portal\Database\Migrations\v010\CountriesTable',
        '\UserFrosting\Sprinkle\Portal\Database\Migrations\v010\ExpertisesTable',
        '\UserFrosting\Sprinkle\Portal\Database\Migrations\v010\UniversitiesTable'
    ];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('applications')) {
            $this->schema->create('applications', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->nullable(false);
                $table->integer('country_id')->unsigned()->nullable(false);
                $table->integer('expertise_id')->unsigned()->nullable(false);
                $table->integer('university_id')->unsigned()->nullable(false);
                $table->integer('year')->unsigned()->nullable(false);
                $table->date('birthday')->nullable(false);
                $table->string('phone')->nullable();
                $table->string('street')->nullable(false);
                $table->integer('postal_code')->nullable(false);
                $table->string('city')->nullable(false);
                $table->string('state')->nullable(false);
                $table->string('facebook')->nullable();
                $table->string('twitter')->nullable();
                $table->string('googleplus')->nullable();
                $table->string('github')->nullable();
                $table->string('xing')->nullable();
                $table->string('linkedin')->nullable();
                $table->string('referrer')->nullable();
                $table->string('dietary_restrictions')->nullable();
                $table->string('comment')->nullable();
                $table->string('teammate1')->nullable();
                $table->string('teammate2')->nullable();
                $table->string('teammate3')->nullable();
                $table->boolean('tos_accepted')->nullable(false)->default(false);
                $table->boolean('flag_accepted')->nullable(false)->default(false);
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('country_id')->references('id')->on('countries');
                $table->foreign('expertise_id')->references('id')->on('expertises');
                $table->foreign('university_id')->references('id')->on('universities');
                $table->index('user_id');
                $table->index('country_id');
                $table->index('university_id');
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('applications');
    }
}
