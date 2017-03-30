# SharinPix PHP Library

This is a work in progress.

Install with composer:

```json
{
    "require": {
        "sharinpix/sharinpix": "*",
    }
}
```

Here is an example :

```php
use sharinpix\Client as SharinpixClient;

$client = new SharinpixClient();
$id = 'super_album_test';
var_dump($client->call_api('GET', "albums/$id"));
```
