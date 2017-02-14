<p align="center">
<a href="https://travis-ci.org/seancheung/access"><img src="https://travis-ci.org/seancheung/access.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/panoscape/access"><img src="https://poser.pugx.org/panoscape/access/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/panoscape/access"><img src="https://poser.pugx.org/panoscape/access/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/panoscape/access"><img src="https://poser.pugx.org/panoscape/access/license.svg" alt="License"></a>
</p>

# Access
Role and Permission control for Laravel

## Installation

### Composer

```shell
composer require panoscape/access
```

### Service provider

> config/app.php

```php
'providers' => [
    ...
    Panoscape\Access\AccessServiceProvider::class,
];
```

### Facades

> config/app.php

```php
'aliases' => [
    ...
    'Access' => Panoscape\Access\Facades\Access::class,
];
```

### Permission and Role

> config/app.php

```php
'aliases' => [
    ...
    'App\Permission' => Panoscape\Access\Permission::class,
    'App\Role' => Panoscape\Access\Role::class,
];
```

### Middleware

> app/Http/Kernel.php

```php
protected $routeMiddleware = [
  ...
  'access' => \Panoscape\Access\Middleware\VerifyAccess::class,
];
```

### Migration

```shell
php artisan vendor:publish --provider="Panoscape\Access\AccessServiceProvider" --tag=migrations
```

Before migrating, you'll need to modify the `users` table in the published migration file to the correct user table used in your application

```php
//TODO: user table
$table->foreign('user_id')->references('id')->on('users')
```

## Usage

Add `HasRoles` trait to user model.

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Panoscape\Access\HasRoles;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasRoles;
}
```

### Get roles and permissions

```php
$user->roles();
$user->permissions();

$role->permissions();
$role->users();

$permission->roles();
```

You may also use dynamic properties

```php
$user->roles;
$user->permissions;

$role->permissions;
$role->users;

$permission->roles;
```

### Check roles and permissions

```php
//return true if the user has this role
$user->hasRoles('admin');
//return true if the user has all three roles
$user->hasRoles(['admin', 'editor', 'author']);
//equivalent to array
$user->hasRoles('admin|editor|author');
//return true if the user has any of the three roles
$user->hasRoles(['admin', 'editor', 'author'], false);
//by default it checks 'name' column; you may specify which column to check
$user->hasRoles([1, 3, 12], true, 'id');

//or check permissions
$user->hasPermissions('edit_users');

//also available on role
$role->hasPermissions('edit_users');
```

### Attach/Detach/Sycn

```php
//by name
$user->attachRoles('admin');
//by id
$user->attachRoles(1);
//by model instance
$user->attachRoles($role);
//with array
$user->attachRoles(['admin', 'editor']);
$user->attachRoles([1, 2]);
//specify column
$user->attachRoles(['1', '2'], 'id');

//detach
$user->detachRoles('admin');
//detach all
$user->detach([]);

//sync
$user->syncRoles('admin');
//detach all
$user->syncRoles([]);
//sync without detaching
$user->syncRoles(['admin', 'editor'], false);

//same with role and permissions
$role->attachPermissions('editor_users');
$role->detachPermissions('editor_users');
$role->syncPermissions('editor_users');

```

### Facades

```php
//check the current authenticated user's roles and permissions
Access::hasRoles('admin');
Access::hasPermissions('edit_users');

//check the given user's roles and permissions
Access::hasRoles('admin', true, 'name', $user);
Access::hasPermissions('edit_users', true, 'name', $user);

//attach roles to current authenticated user
Access::attachRoles('admin');
Access::attachRoles(['admin', 'editor']);

//attach roles to the given user
Access::attachRoles('admin', 'name', $user);
Access::attachRoles(['admin', 'editor'], 'name', $user);
```

### Middleware

```php
//role
Route::get('/dashboard', 'DashboardController@index')->middleware('access:roles,admin');
//permission
Route::get('/dashboard', 'DashboardController@index')->middleware('access:permissions,edit_users');
//multiple
Route::get('/dashboard', 'DashboardController@index')->middleware('access:permissions,edit_users|manage_sites');
//requirement and column
Route::get('/dashboard', 'DashboardController@index')->middleware('access:permissions,1|3,false,id');
```

### Blade

```html
@roles('admin')
<div>
    ...
<div>
@endroles

@permissions('edit_users|Manage_sites')
<div>
    ...
<div>
@endpermissions

@roles('admin|editor|root', false)
<div>
    ...
<div>
@endroles

@permissions('1|2|3', false, 'id')
<div>
    ...
<div>
@endpermissions
```

## Testing

```shell
composer test
```

or

```shell
vendor/bin/phpunit
```


## Change Log

### [1.0.1] - 2016-11-24
#### Added
- attach/detach/sync roles and permissions in Facades
- Facades now accept specific user rather than always using `auth()->user()`
- detaching roles when deleting users
- detaching permissions when deleting roles
- unit test

#### Changed
- renamed `access` middleware's first argument to plural format: `role` to `roles`, `permission` to `permissions`
- multiple arguments in blade directives no longer need to be wrapped in an array

#### Fixed
- `roles` and `permissions` blade directives failure issue

### [1.0.0] - 2016-11-22
#### First release

[1.0.1]: https://github.com/panoscape/access/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/panoscape/access/releases/tag/1.0.0