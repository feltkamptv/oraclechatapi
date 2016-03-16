## Oracle Chat API PHP class

The Oracle Chat API PHP class is a simple class for making simple calls to the Oracle Chat SOAP API of Oracle Service Cloud with PHP.

## Installation

Download the oraclesoapapi.php file and include this in the PHP file where you would like to use the Oracle Chat API.

## Usage

```php
include_once('oraclesoapapi.php');

$soapUrl = 'https://your_service_cloud.rightnowdemo.com/cgi-bin/your_service_cloud.cfg/services/chat_soap';
$username = 'YOUR_USERNAME';
$password = 'YOUR_PASSWORD';
$app_id = 'YOUR_APP_ID';
$interface_name = 'your_service_cloud';
$interface_id = 1;

$client = new oracleSoapApi($soapUrl, $username, $password, $app_id, false, $interface_id, $interface_name);
```

If you are communicating with the Oracle Service Cloud Chat API, make sure you have Curl compiled with OpenSSL and not with GnuTLS. GnuTLS tends to give errors because the Chat API does not properly close TLS connections.

## Need support?

If you need support with Oracle Service Cloud or with this Chat API PHP class, contact [Customer Interaction Group](http://www.custintgroup.com) or [Feltkamp.tv](http://www.feltkamp.tv)

## License

This code is licensed under the GNU license.
