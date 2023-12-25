# whatsapp-php-client
A php client for the whatsapp web multi device api

## Usage

```php 
    $dsn = 'sqlite:' . __DIR__ . '/../test.sqlite';
    $container = new Container('sqlite', $dsn);
    
    $deviceStore = $container->getFirstDevice();
    $client = new Client($deviceStore);
    
    // be able to add a function of callable type to handle different events on the client
    $client->addEventHandler('eventHandler');
    
    if ($client->store->ID === null) {
        // print new qr code
        // when the code is scanned, log user in
        
     } else {
        $client->connect();
     }
     
     // block the main thread till user exits
```

