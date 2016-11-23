<?php

namespace Panoscape\Access\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Panoscape\Access\Role;
use Panoscape\Access\Permission;
use Panoscape\Access\Facades\Access;
use Panoscape\Access\Middleware\VerifyAccess;

class TestCaseTest extends TestCase
{
    protected $root;

    protected $user;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['router']->middleware('access', VerifyAccess::class);

        $app['router']->get('users', function() {
            return 'users page';
        })->middleware('access:role,admin');

        $app['router']->post('users', function() {
            return 'created new user';
        })->middleware('access:permission,edit_users');

        $app['router']->get('articles', function() {
            return 'articles page';
        })->middleware('access:role,admin|editor');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Panoscape\Access\AccessServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Access' => \Panoscape\Access\Facades\Access::class,
        ];
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->root = User::create(['name' => 'root']);
        $this->user = User::create(['name' => 'user']);

        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--realpath' => realpath(__DIR__.'/../src/migrations'),
        ]);

        $manage_users = [
            'index_users' => Permission::create(['name' => 'index_users'])->id,
            'edit_users' => Permission::create(['name' => 'edit_users'])->id,
            'create_users' => Permission::create(['name' => 'create_users'])->id,
            'delete_users' => Permission::create(['name' => 'delete_users'])->id,
        ];

        $manage_articles = [
            'index_articles' => Permission::create(['name' => 'index_articles'])->id,
            'edit_articles' => Permission::create(['name' => 'edit_articles'])->id,
            'create_articles' => Permission::create(['name' => 'create_articles'])->id,
            'delete_articles' => Permission::create(['name' => 'delete_articles'])->id,
        ];

        $admin = Role::create(['name' => 'admin']);
        $admin->permissions()->sync($manage_users);
        $editor = Role::create(['name' => 'editor']);
        $editor->permissions()->sync($manage_articles);

        $this->root->roles()->sync([$admin->id, $editor->id]);        
        $this->user->roles()->sync([$editor->id]);        
    }

    /**
     * Test roles.
     *
     * @test
     */
    public function testRoles()
    {
        $this->assertNotNull($this->root);
        $this->assertEquals($this->root->roles()->count(), 2);
        $this->assertTrue($this->root->hasRoles('admin'));
        $this->assertFalse($this->root->hasRoles('author'));
        $this->assertTrue($this->root->hasRoles(['admin', 'editor']));
        $this->assertTrue($this->root->hasRoles('admin|editor'));
        $this->assertFalse($this->root->hasRoles(['admin', 'editor', 'author']));
        $this->assertTrue($this->root->hasRoles(['admin', 'editor', 'author'], false));
        $this->assertTrue($this->root->hasRoles('admin|editor|author', false));
    }

    /**
     * Test permissions.
     *
     * @test
     */
    public function testPermissions()
    {
        $this->assertNotNull($this->root);
        $this->assertEquals($this->root->permissions()->count(), 8);
        $this->assertTrue($this->root->hasPermissions('index_users'));
        $this->assertFalse($this->root->hasPermissions('post_comments'));
        $this->assertTrue($this->root->hasPermissions(['index_users', 'edit_articles']));
        $this->assertTrue($this->root->hasPermissions('index_users|edit_articles'));
        $this->assertFalse($this->root->hasPermissions(['index_users', 'edit_articles', 'post_comments']));
        $this->assertTrue($this->root->hasPermissions(['index_users', 'edit_articles', 'post_comments'], false));
        $this->assertTrue($this->root->hasPermissions('index_users|edit_articles|post_comments', false));
    }

    /**
     * Test Facades.
     *
     * @test
     */
    public function testFacades()
    {
        $this->actingAs($this->user)->assertFalse(Access::hasRoles('admin'));
        $this->actingAs($this->root)->assertTrue(Access::hasRoles('admin'));
        $this->actingAs($this->root)->assertTrue(Access::hasRoles('admin|editor'));
        $this->actingAs($this->user)->assertFalse(Access::hasRoles('admin|editor'));
        $this->actingAs($this->user)->assertFalse(Access::hasPermissions('edit_users'));
        $this->actingAs($this->root)->assertTrue(Access::hasPermissions('edit_users'));
    }

    /**
     * Test middleware.
     *
     * @test
     */
    public function testMiddleware()
    {
        // $this->actingAs($this->user)->get('/users')->assertResponseStatus(403);
        // $this->actingAs($this->root)->visit('/users')->see('users page');

        // $this->actingAs($this->user)->post('/users')->assertResponseStatus(403);
        // $this->actingAs($this->root)->post('/users')->see('created new user');

        $this->actingAs($this->user)->get('/articles')->see('articles page');
        $this->actingAs($this->root)->visit('/articles')->see('articles page');
    }
}