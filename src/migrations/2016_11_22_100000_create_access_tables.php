<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->unique();
            $table->string('fullname')->nullable();
            $table->string('description')->nullable();
        });

        Schema::create('access_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->unique();
            $table->string('fullname')->nullable();
            $table->string('description')->nullable();
        });

        Schema::create('access_permission_role', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();            
            
            $table->foreign('permission_id')->references('id')->on('access_permissions')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('access_roles')
                ->onUpdate('cascade')->onDelete('cascade');            

            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('access_role_user', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            
            $table->foreign('role_id')->references('id')->on('access_roles')
                ->onUpdate('cascade')->onDelete('cascade');

            //TODO: user table
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['role_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_permissions');
        Schema::dropIfExists('access_roles');
        Schema::dropIfExists('access_permission_role');
        Schema::dropIfExists('access_role_user');
    }
}
