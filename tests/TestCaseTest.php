<?php

namespace Panoscape\Access\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Database\Schema\Blueprint;

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
        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['router']->middleware('access', \Panoscape\Access\Middleware\VerifyAccess::class);

        $app['router']->get('users', function() {
            return 'users page';
        })->middleware('access:roles,admin');

        $app['router']->post('users', function() {
            return 'created new user';
        })->middleware('access:permissions,edit_users');

        $app['router']->get('articles', function() {
            return 'articles page';
        })->middleware('access:roles,admin|editor,false');

        $app['router']->get('menu', function() {
            return view()->file(__DIR__.'/view.blade.php');
        });
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
            'App\Role' => \Panoscape\Access\Role::class,
            'App\Permission' => \Panoscape\Access\Permission::class,
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
            'index_users' => \App\Permission::create(['name' => 'index_users'])->id,
            'edit_users' => \App\Permission::create(['name' => 'edit_users'])->id,
            'create_users' => \App\Permission::create(['name' => 'create_users'])->id,
            'delete_users' => \App\Permission::create(['name' => 'delete_users'])->id,
        ];

        $manage_articles = [
            'index_articles' => \App\Permission::create(['name' => 'index_articles'])->id,
            'edit_articles' => \App\Permission::create(['name' => 'edit_articles'])->id,
            'create_articles' => \App\Permission::create(['name' => 'create_articles'])->id,
            'delete_articles' => \App\Permission::create(['name' => 'delete_articles'])->id,
        ];

        $admin = \App\Role::create(['name' => 'admin']);
        $admin->permissions()->sync($manage_users);
        $editor = \App\Role::create(['name' => 'editor']);
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
        $this->assertNotNull($this->user);
        $this->assertEquals($this->root->roles()->count(), 2);
        $this->assertEquals($this->user->roles()->count(), 1);
        $this->assertTrue($this->root->hasRoles('admin'));
        $this->assertFalse($this->user->hasRoles('admin'));
        $this->assertTrue($this->user->hasRoles('editor'));
        $this->assertTrue($this->root->hasRoles(['admin', 'editor']));
        $this->assertTrue($this->root->hasRoles('admin|editor'));
        $this->assertFalse($this->user->hasRoles(['admin', 'editor']));
        $this->assertTrue($this->user->hasRoles(['admin', 'editor'], false));
        $this->assertTrue($this->user->hasRoles('admin|editor', false));

        $this->user->attachRoles('admin');
        $this->assertTrue($this->user->hasRoles('admin'));
        $this->assertTrue($this->user->hasRoles('admin|editor'));
        $this->user->detachRoles(['admin', 'editor']);
        $this->assertFalse($this->user->hasRoles('admin|editor', false));
        $this->user->syncRoles('editor');
        $this->assertTrue($this->user->hasRoles('admin|editor', false));

        $role = \App\Role::where('name', 'editor')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissions('index_articles'));
        $this->assertFalse($role->hasPermissions('index_users'));
        $this->assertFalse($role->hasPermissions('index_articles|index_users'));
        $this->assertTrue($role->hasPermissions('index_articles|index_users', false));
    }

    /**
     * Test permissions.
     *
     * @test
     */
    public function testPermissions()
    {
        $this->assertNotNull($this->root);
        $this->assertNotNull($this->user);
        $this->assertEquals($this->root->permissions()->count(), 8);
        $this->assertEquals($this->user->permissions()->count(), 4);
        $this->assertTrue($this->root->hasPermissions('index_users'));
        $this->assertFalse($this->user->hasPermissions('index_users'));
        $this->assertTrue($this->root->hasPermissions(['index_users', 'edit_articles']));
        $this->assertTrue($this->root->hasPermissions('index_users|edit_articles'));
        $this->assertFalse($this->user->hasPermissions(['index_users', 'edit_articles']));
        $this->assertTrue($this->user->hasPermissions(['index_users', 'edit_articles'], false));
        $this->assertTrue($this->user->hasPermissions('index_users|edit_articles', false));

        $this->user->attachRoles('admin');
        $this->assertTrue($this->user->hasPermissions('index_users'));
        $this->assertTrue($this->user->hasPermissions(['index_users', 'edit_articles']));
        $this->user->syncRoles([]);
        $this->assertFalse($this->user->hasPermissions('index_users|edit_articles', false));
        $this->user->syncRoles('editor');
        $this->assertTrue($this->user->hasPermissions('index_users|edit_articles', false));
    }

    /**
     * Test Facades.
     *
     * @test
     */
    public function testFacades()
    {
        $this->actingAs($this->user)->assertFalse(\Access::hasRoles('admin'));
        $this->actingAs($this->root)->assertTrue(\Access::hasRoles('admin'));
        $this->actingAs($this->root)->assertTrue(\Access::hasRoles('admin|editor'));
        $this->actingAs($this->user)->assertFalse(\Access::hasRoles('admin|editor'));
        $this->actingAs($this->user)->assertTrue(\Access::hasRoles('admin|editor', false));
        $this->actingAs($this->user)->assertFalse(\Access::hasPermissions('edit_users'));
        $this->actingAs($this->root)->assertTrue(\Access::hasPermissions('edit_users'));
        $this->actingAs($this->root)->assertTrue(\Access::hasPermissions('edit_users|edit_articles'));
        $this->actingAs($this->user)->assertFalse(\Access::hasPermissions('edit_users|edit_articles'));
        $this->actingAs($this->user)->assertTrue(\Access::hasPermissions('edit_users|edit_articles', false));
    }

    /**
     * Test middleware.
     *
     * @test
     */
    public function testMiddleware()
    {
        $this->actingAs($this->user)->get('/users')->assertResponseStatus(403);
        $this->actingAs($this->root)->get('/users')->see('users page');

        $this->actingAs($this->user)->post('/users')->assertResponseStatus(403);
        $this->actingAs($this->root)->post('/users')->see('created new user');

        $this->actingAs($this->user)->get('/articles')->see('articles page');
        $this->actingAs($this->root)->get('/articles')->see('articles page');
    }

    /**
     * Test blade.
     *
     * @test
     */
    public function testBlade()
    {
        $this->actingAs($this->user)->get('/menu')->see('editor_panel')->dontSee('admin_panel')->see('articles')->dontSee('users')->dontSee('comments');
        $this->actingAs($this->root)->get('/menu')->see('editor_panel')->see('admin_panel')->see('articles')->see('users')->see('comments');
    }

    /**
     * Test blade.
     *
     * @test
     */
    public function testDelete()
    {
        // User::whereIn('name', ['root', 'user'])->delete();
        User::all()->each(function($user) { $user->delete(); });
        $this->assertEquals(User::count(), 0);
        $this->assertEquals($this->root->roles()->count(), 0);
        $this->assertEquals($this->user->roles()->count(), 0);
    }
}