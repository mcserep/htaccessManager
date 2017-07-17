# A .htaccess manager

Simple web editor of htpasswd files (basic authentication) for repository management. Users may alter their passwords, administrators can add, delete and modify users.

Example usage assuming authentication was enforced on page load by htaccess:
```php
require_once 'htaccessManager.php';
$manager = new htaccessManager(
    'example',                 // required; name of the repository, serves as a title
    array('admin1', 'admin2'), // optional; users with administrator privileges
    '/path/to/passwd/folder',  // optional; the location of .htpasswd file, 
                               // defaults to the value defined in htaccessManager.php
    'htpasswd file name'       // optional; name of the .htpasswd file, 
                               // defaults to the repository name
);
$manager->show($_SERVER['PHP_AUTH_USER']); 
```
