<?php

/**
 * Razorgator PHP Client Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/teamonetickets/razorgator-php/blob/master/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@teamonetickets.com so we can send you a copy immediately.
 *
 * @category    Razorgator
 * @package     Razorgator\Client
 * @author      J Cobb <j@teamonetickets.com>
 * @copyright   Copyright (c) 2013 Team One Tickets & Sports Tours, Inc. (http://www.teamonetickets.com)
 * @license     https://github.com/teamonetickets/razorgator-php/blob/master/LICENSE.txt    BSD 3-Clause License
 */


namespace Razorgator;


/**
 * @category    Razorgator
 * @package     Razorgator\Client
 * @copyright   Copyright (c) 2013 Team One Tickets & Sports Tours, Inc. (http://www.teamonetickets.com)
 * @license     https://github.com/teamonetickets/razorgator-php/blob/master/LICENSE.txt    BSD 3-Clause License
 */
class Client
{
    /**
     * Razorgator API Token
     *
     * To get your API security token, follow these steps:
     * 1. Log in to Pearl @ https://pearl.razorgator.com/
     * 2. Change the address to: https://pearl.razorgator.com/AboutApi.aspx
     * 3. Copy your token into a safe place, then hit the back button to go back to Pearl’s home page.
     *
     * @var string
     */
    public $apiToken;

    /**
     * Base URI for the REST client
     *
     * @var string
     */
    protected $_baseUri = 'https://pearl.razorgator.com';

    /**
     * API version
     *
     * @var string
     */
    protected $_apiVersion = '1';


    /**
     * Reference to REST client object
     *
     * @var Zend_Rest_Client
     */
    protected $_rest = null;


    /**
     * Whether or not to use persistent connections.
     *
     * @var bool
     */
    protected $_usePersistentConnections = true;


    /**
     * Defines how the data is returned.
     * * resultset      = Default. An iterable TicketEvolution\Client\Resultset object
     * * xml            = The XML received with no conversion
     *
     * @var string [resultset,xml]
     */
    public $resultType = 'resultset';


    /**
     * Constructs a new Razorgator Web Services Client
     *
     * @param  mixed $config  An array or Zend_Config object with adapter parameters.
     * @throws Client\Exception
     * @return TicketEvolution\Client
     */
    public function __construct($config)
    {
        if ($config instanceof \Zend_Config) {
            $config = $config->toArray();
        }

        /*
         * Verify that parameters are in an array.
         */
        if (!is_array($config)) {
            throw new Client\Exception(
                'Parameters must be in an array or a Zend_Config object'
            );
        }

        /*
         * Verify that an API token has been specified.
         */
        if (!is_string($config['apiToken']) || empty($config['apiToken'])) {
            throw new Client\Exception(
                'API token must be specified in a string'
            );
        }

        /*
         * See if we need to override the API version.
         */
        if (!empty($config['apiVersion'])) {
            $this->_apiVersion = (string) $config['apiVersion'];
        }

        /*
         * See if we need to override the base URI.
         */
        if (!empty($config['baseUri'])) {
            $this->_baseUri = (string) $config['baseUri'];
        }

        /*
         * See if we need to override the _usePersistentConnections.
         */
        if (isset($config['usePersistentConnections'])) {
            $this->_usePersistentConnections = (bool) $config['usePersistentConnections'];
        }

        $this->apiToken = (string) $config['apiToken'];

        $this->_apiPrefix = '/WebserviceV' . $this->_apiVersion . '.asmx/';
    }


    /**
     * List Orders
     *
     * @param  array $options Options to use for the search query
     * @throws Client\Exception
     * @return TicketEvolution\Client\ResultSet
     */
    public function listOrders(array $options)
    {
        $endPoint = 'GetOrders';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $defaultOptions = array(
            'status' => 'UNCONFIRMED'
        );
        $options = $this->_prepareOptions(
            'GET',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restGet($this->_apiPrefix . $endPoint, $options);

        return $this->_postProcess($response);
    }


    /**
     * List Orders
     *
     * @param  array $options Options to use for the search query
     * @throws Client\Exception
     * @return TicketEvolution\Client\ResultSet
     */
    public function listOrdersCompleted(array $options)
    {
        $endPoint = 'GetCompletedOrders';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'GET',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restGet($this->_apiPrefix . $endPoint, $options);

        return $this->_postProcess($response);
    }


    /**
     * Get a single order by Id
     *
     * @param  int $orderId
     * @throws Client\Exception
     * @return stdClass
     */
    public function showOrder($orderId)
    {
        $endPoint = 'GetOrder';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $options = array(
            'orderId'   => (int) $orderId,
        );
        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'GET',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restGet($this->_apiPrefix . $endPoint, $options);

        return $this->_postProcess($response);
    }


    /**
     * Accept an order
     *
     * @param int $orderId ID of the order to accept
     * @param array $options Options to use for the search query
     * @throws Client\Exception
     * @return stdClass
     */
    public function acceptOrder($orderId, $options)
    {
        $endPoint = 'ConfirmOrder';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $options['orderId'] = (int) $orderId;
        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'POST',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restPost($this->_apiPrefix . $endPoint, $options);

        return $this->_postProcess($response);
    }




    /**
     * Ship an order
     *
     * @param int $orderId ID of the order to accept
     * @param array $options Options to use
     * @throws Client\Exception
     * @return stdClass
     */
    public function shipOrder($orderId, $options)
    {
        $endPoint = 'ShipOrder';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $options['orderId'] = (int) $orderId;
        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'POST',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restPost($this->_apiPrefix . $endPoint, $options);

        return $this->_postProcess($response);
    }


    /**
     * Reject an order
     *
     * @param int $orderId The order id
     * @param string $orderToken The unique order token associated with the order
     * @throws Client\Exception
     * @return stdClass
     */
    public function rejectOrder($orderId, $orderToken)
    {
        $endPoint = 'RejectOrder';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $options = array(
            'orderId'           => (int)    $orderId,
            'orderToken'        => (string) $orderToken,
        );
        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'POST',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restPost($this->_apiPrefix . $endPoint, $options);

        return $this->_postProcess($response);
    }


    /**
     * Get an airbill (Base64 encoded) and tracking number
     *
     * @param int $orderId The order id
     * @param string $orderToken The unique order token associated with the order
     * @param int $purchaseOrderId The purchase order id
     * @throws Client\Exception
     * @return stdClass
     */
    public function getAirbill($orderId, $orderToken, $purchaseOrderId)
    {
        $endPoint = 'GetAirbill';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $options = array(
            'orderId'           => (int)    $orderId,
            'orderToken'        => (string) $orderToken,
            'purchaseOrderId'   => (int)    $purchaseOrderId,
        );
        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'GET',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restGet($this->_apiPrefix . $endPoint, $options);

        return $this->_postProcess($response);
    }


    /**
     * Get an airbill as a PDF file
     *
     * @param int $orderId The order id
     * @param string $orderToken The unique order token associated with the order
     * @param int $purchaseOrderId The purchase order id
     * @throws Client\Exception
     * @return string (The PDF file of the airbill)
     */
    public function downloadAirbill($orderId, $orderToken, $purchaseOrderId)
    {
        $endPoint = 'DownloadAirbill';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $options = array(
            'orderId'           => (int)    $orderId,
            'orderToken'        => (string) $orderToken,
            'purchaseOrderId'   => (int)    $purchaseOrderId,
        );
        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'GET',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restGet($this->_apiPrefix . $endPoint, $options);

        return $response->getBody();
    }


    /**
     * Get a Purchase Order (Base64 encoded) and Purchase Order ID
     *
     * @param int $purchaseOrderId The purchase order id
     * @param int $orderId The order id
     * @param string $orderToken The unique order token associated with the order
     * @throws Client\Exception
     * @return stdClass
     */
    public function getPurchaseOrder($purchaseOrderId, $orderId, $orderToken)
    {
        $endPoint = 'GetPurchaseOrder';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $options = array(
            'orderId'           => (int)    $orderId,
            'orderToken'        => (string) $orderToken,
            'purchaseOrderId'   => (int)    $purchaseOrderId,
        );
        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'GET',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restGet($this->_apiPrefix . $endPoint, $options);

        return $this->_postProcess($response);
    }


    /**
     * Get a Purchase Order as a PDF file
     *
     * @param int $purchaseOrderId The purchase order id
     * @param int $orderId The order id
     * @param string $orderToken The unique order token associated with the order
     * @throws Client\Exception
     * @return string (The PDF file of the Purchase Order)
     */
    public function downloadPurchaseOrder($purchaseOrderId, $orderId, $orderToken)
    {
        $endPoint = 'DownloadAirbill';

        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $options = array(
            'orderId'           => (int)    $orderId,
            'orderToken'        => (string) $orderToken,
            'purchaseOrderId'   => (int)    $purchaseOrderId,
        );
        $defaultOptions = array();
        $options = $this->_prepareOptions(
            'GET',
            $endPoint,
            $options,
            $defaultOptions
        );

        $client->getHttpClient()->resetParameters();
        $this->_setHeaders(
            $this->apiToken,
            $this->_apiVersion
        );

        $response = $client->restGet($this->_apiPrefix . $endPoint, $options);

        return $response->getBody();
    }


    /**
     * Returns a reference to the REST client
     *
     * @return Zend_Rest_Client
     */
    public function getRestClient()
    {
        if ($this->_rest === null) {
            $this->_rest = new \Zend_Rest_Client();

            $httpClient = new \Zend_Http_Client(
                $this->_baseUri,
                array(
                    'keepalive' => $this->_usePersistentConnections
                )
            );


            /**
             * The Razorgator Sandbox uses a self-signed certificate which,
             * by default is not allowed. If we are using https in the sandbox lets
             * tweak the options to allow this self-signed certificate.
             *
             * @link http://framework.zend.com/manual/en/zend.http.client.adapters.html Example 2
             */
            if (strpos($this->_baseUri, 'sandbox') !== false) {
                $streamOptions = array(
                    // Verify server side certificate,
                    // Accept self-signed SSL certificate
                    'ssl' => array(
                        //'verify_peer' => true,
                        'allow_self_signed' => true,
                    )
                );
            } else {
                $streamOptions = array();
            }

            /**
             * Create an adapter object and attach it to the HTTP client
             *
             * @see Zend_Http_Client_Adapter_Socket
             */
            $adapter = new \Zend_Http_Client_Adapter_Socket();

            $adapterConfig = array (
                'persistent'    => $this->_usePersistentConnections,
            );
            $adapter->setConfig($adapterConfig);

            $httpClient->setAdapter($adapter);

            // Pass the streamOptions array to setStreamContext()
            $adapter->setStreamContext($streamOptions);

            $this->_rest->setHttpClient($httpClient);
        }
        return $this->_rest;
    }


    /**
     * Set REST client
     *
     * @param Zend_Rest_Client
     * @return TicketEvolution\Client
     */
    public function setRestClient(\Zend_Rest_Client $client)
    {
        $this->_rest = $client;
        return $this;
    }


    /**
     * Set special headers for request
     *
     * @param  string  $apiToken
     * @param  string  $apiVersion
     * @return void
     */
    protected function _setHeaders($apiToken, $apiVersion, $requestSignature=null)
    {
        $headers = array(
            'User-Agent'    => 'teamonetickets/razorgator-php',
            'Accept'        => 'application/xml',
        );

        $this->_rest->getHttpClient()->setHeaders($headers);
    }


    /**
     * Prepare options for request
     *
     * @param  string $action         Action to perform [GET|POST|PUT|DELETE]
     * @param  array  $endPoint       The endPoint
     * @param  array  $options        User supplied options
     * @param  array  $defaultOptions Default options
     * @return array
     */
    protected function _prepareOptions($action, $endPoint, array $options, array $defaultOptions)
    {
        $options = array_merge($defaultOptions, $options, array('apiToken' => $this->apiToken));
        ksort($options);

        return $options;
    }


    /**
     * Allows post-processing logic to be applied.
     * Subclasses may override this method.
     *
     * @param string $responseBody The response body to process
     * @return void
     */
    protected function _postProcess($response)
    {

        /**
         * Uncomment for debugging to see the actual request and response
         * or in your code use
         * $tevo->getRestClient()->getHttpClient()->getLastRequest() and
         * $tevo->getRestClient()->getHttpClient()->getLastResponse()
         */
        /**
        echo PHP_EOL;
        var_dump($this->getRestClient()->getHttpClient()->getLastRequest());
        echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
        echo PHP_EOL;
        var_dump($this->getRestClient()->getHttpClient()->getLastResponse());
        echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
         */


        if ($response->isError()) {
            throw new Client\Exception(
                'An error occurred sending request. Status code: '
                . $response->getStatus()
            );
        }


        switch ($this->resultType) {
            case 'xml':
                return simplexml_load_string($response->getBody());
                break;

            case 'resultset':
            default:
                $xml = new \SimpleXMLElement($response->getBody());

                // If the name is "orders" it should be an array of orders so return an ResultSet
                if ($xml->getName() === 'orders') {
                    unset($xml);
                    return new Client\ResultSet($response->getBody());
                } else {
                    return $xml;
                }

        }

        return false;
    }


    /**
     * Utility method used to catch problems decoding the JSON.
     *
     * @param string $string
     * @return mixed
     * @link http://php.net/manual/en/function.json-decode.php
     */
    public static function xml_decode($string)
    {
        $decodedXml = xml_decode($string);

        if (is_null($decodedXml)) {
            throw new Webserivce\Exception(
                'An error occurred decoding the XML received: ' . xml_last_error()
            );
        }

        return $decodedXml;
    }


}
