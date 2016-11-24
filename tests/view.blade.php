<!DOCTYPE html>
<html lang="en">
    <body>
        @roles('admin')
        <h1>admin_panel</h1>
        @endroles
        @roles('admin|editor', false)
        <h1>editor_panel</h1>
        @endroles
        @permissions('index_articles')
        <h1>articles</h1>
        @endpermissions
        @permissions('index_users')
        <h1>users</h1>
        @endpermissions
        @permissions('index_users|index_articles')
        <h1>comments</h1>
        @endpermissions
    </body>
</html>
