Synapse-Files
=============

Installation: Add to composer.json and composer update!

Setup
-----

To use: register the following services in your application:
```
    $app->register(new AwsServiceProvider());
    $app->register(new AwsCredentialsServiceProvider());
    $app->register(new FileServiceProvider());
```

Create a file.php config file with the following contents:
```
return [
    'base_path'  => realpath(__DIR__.'/..').'/files',
];
```

For production, file config should be:
```
<?php
return [
    'bucket'     => 'some-existing-bucket',
    'base_path'  => 'files',
];
```

The following environment variables MUST exist when using S3
```
AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY
AWS_REGION
```
