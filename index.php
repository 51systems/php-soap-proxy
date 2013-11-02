<?php
require_once 'vendor/autoload.php';


$config = require_once('config/config.php');

$request = new \Zend\Http\PhpEnvironment\Request();

//Calculate the destination location
$location = $request->getQuery('location');
if ($location) {
    $soapEndPoint = urldecode($location);
} else {
    $soapEndPoint = $config['base-url'] . $_SERVER['REQUEST_URI'];
}
$request->getQuery()->offsetUnset('location');

$request->setServer(new \Zend\Stdlib\Parameters());
$request->setUri($soapEndPoint);
$request->getHeaders()->removeHeader(new \Zend\Http\Header\Host());

$client = new \Zend\Http\Client(null, $config['http-client']);

/** @var \Zend\Http\Response $remoteResponse */
$remoteResponse = $client->dispatch($request);
$response = new \Zend\Http\PhpEnvironment\Response();

//Copy headers to the response going down to the client
if (isset($config['response']['header']['pass-through'])) {
    $remoteHeaders = $remoteResponse->getHeaders();
    $localHeaders = $response->getHeaders();

    foreach ($config['response']['header']['pass-through'] as $headerName) {
        if ($remoteHeaders->has($headerName)) {
            $header = $remoteHeaders->get($headerName);

            if ($header instanceof \Zend\Http\Header\HeaderInterface) {
                $localHeaders->addHeader($header);
            } else {
                $localHeaders->addHeaders($header);
            }
        }
    }
}

//copy the content over
$response->setContent($remoteResponse->getContent());

$response->send();


