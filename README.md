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
php artisan vendor:publish --provider="Panoscape\Access\AccessServiceProvider" --tag="migrations"
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
by model instance
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
```

### Middleware

```php
//role
Route::get('/dashboard', 'DashboardController@index')->middleware('access:role,admin');
//permission
Route::get('/dashboard', 'DashboardController@index')->middleware('access:permission,edit_users');
//multiple
Route::get('/dashboard', 'DashboardController@index')->middleware('access:permission,edit_users|manage_sites');
//requirement and column
Route::get('/dashboard', 'DashboardController@index')->middleware('access:permission,1|3,false,id');
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

<-- Multiple arguments should be passed in as array -->
@roles(['admin|editor|root', false])
<div>
    ...
<div>
@endroles

@permissions(['1|2|3', false, 'id'])
<div>
    ...
<div>
@endpermissions
```
