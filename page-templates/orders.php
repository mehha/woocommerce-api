<?php
/**
 * Created by PhpStorm.
 * User: masluk
 * Date: 12.04.2018
 * Time: 10:19
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require 'config.php';
require 'ArrayToXML.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

$woocommerce = new Client(
    $store,
    $client_key,
    $secret_key,
    [
        'wp_api' => true,
        'version' => 'wc/v2',
    ]
);

//TELLIMUSE SAATMINE
function post_order($woocommerce){

        try {
            $results = $woocommerce->get('orders/1929');

            $new_order = [
                'register' => [
                    'record' => [
                        'head' => [
                        ],
                        'rows' => [
                            'row' => []
                        ],
                    ],
                ]
            ];

            $RFC = $results->date_created;
            $date = new DateTime($RFC);
            $dateStr = $date->format('d.m.Y');

            $order_items_one = [
                'RefStr' => $results->id,
                'OrdDate' => $dateStr,
                'CustRegNr1' => '12815090',
                'Addr0' => $results->billing->first_name,
                'Addr1' => $results->billing->address_1,
                'Addr2' => $results->billing->address_2,
                'OurContact' => $results->billing->first_name,
                'CustContact' => $results->billing->first_name,
                'Sum1' => 357.10,
                'Sum3' => 71.42,
                'Sum4' => $results->total,
            ];

            array_push($new_order['register']['record']['head'], $order_items_one);

            foreach ($results->line_items as $line_item) {
                $order_items_two = [
                    'ArtCode' => $line_item->sku,
                    'Quant' => $line_item->quantity,
                    'Price' => $line_item->price,
                    'Spec' => $line_item->name,
                    'Sum' => $line_item->total
                ];

                array_push($new_order['register']['record']['rows']['row'], $order_items_two);
            }

            $xml = new ArrayToXML();
            print $xml->buildXML($new_order);
            $parsed_xml = $xml->buildXML($new_order);


            function order_curl($parsed_xml) {
                //Initialize handle and set options
                $username = 'rest';
                $password = 'passpasspass';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://mars.excellent.ee:2888/WebANTOAPI.hal?register=ORVc&company=1');
                curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 4);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parsed_xml);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));

                //Execute the request and also time the transaction ( optional )
                $start = array_sum(explode(' ', microtime()));
                $result = curl_exec($ch);
                $stop = array_sum(explode(' ', microtime()));
                $totalTime = $stop - $start;

                //Check for errors ( again optional )
                if ( curl_errno($ch) ) {
                    $result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
                } else {
                    $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    switch($returnCode){
                        case 200:
                            break;
                        default:
                            $result = 'HTTP ERROR -> ' . $returnCode;
                            break;
                    }
                }

                //Close the handle
                curl_close($ch);

                //Output the results and time
                echo 'Total time for request: ' . $totalTime . "\n";
                echo $result;
            }
            order_curl($parsed_xml);

        } catch (HttpClientException $e) {
            echo "<p>".$e->getMessage()."</p>"; // Error message.
            $e->getRequest(); // Last request data.
            $e->getResponse(); // Last response data.
        }
    }

post_order($woocommerce);
