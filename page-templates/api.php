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

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;


$url = 'https://mars.excellent.ee:2888/api/1/webngproductvc';

$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Basic " . base64_encode("$username:$password")
    )
));
$data = file_get_contents($url, false, $context);

echo $data;

$newArticles = array();
//while(!feof($raw_data)){
//    $custInfo=(fgetcsv($raw_data));
//    $company=$custInfo[0];
//    $fname=$custInfo[1];
//    $lname=$custInfo[2];
//    $email=$custInfo[3];
//
//    $new_article = [
//        'name' => 'Premium Quality',
//        'type' => 'simple',
//        'regular_price' => '21.99',
//        'description' => '',
//        'short_description' => '',
//        'categories' => [
//            [
//                'id' => 9
//            ],
//            [
//                'id' => 14
//            ]
//        ],
//        'images' => [
//            [
//                'src' => '',
//                'position' => 0
//            ]
//        ]
//    ];
//    array_push($newArticles, $new_article);
//}

//var_dump($newArticles);

$woocommerce = new Client(
    $store,
    $client_key,
    $secret_key,
    [
        'wp_api' => true,
        'version' => 'wc/v2',
    ]
);

//foreach ($newArticles as $customer) {
//    try {
//        $results = $woocommerce->post('customers', $customer);
//        echo "<p>Customer ".$results->email." was created.</p>";
//
//    } catch (HttpClientException $e) {
//        echo "<p>".$e->getMessage()."</p>"; // Error message.
//        $e->getRequest(); // Last request data.
//        $e->getResponse(); // Last response data.
//    }
//}