# SMSGateway

### Installation
```
composer require dreamer1258/sms-gateway
````

### Example
```php
<?php

require 'vendor/autoload.php';

use Dreamer1258\SmsGateway\SmsGateway;

try {
	$gateway = new SmsGateway();
	$gateway->send(123456789, 'SMS Message');
}
catch(SmsGatewayException $e) {
	echo $e-getMessage();
}
```