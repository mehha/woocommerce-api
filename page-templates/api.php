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

$raw_data = fopen(get_stylesheet_directory()."/page-templates/MOCK_DATA.csv", "r");
$newCustomers = array();
while(!feof($raw_data)){
    $custInfo=(fgetcsv($raw_data));
    $company=$custInfo[0];
    $fname=$custInfo[1];
    $lname=$custInfo[2];
    $email=$custInfo[3];

    $new_customer = [
        'email' => $email,
        'first_name' => $fname,
        'last_name' => $lname,
        'username' => $email,
        'password' => '12345',
        'billing' => [
            'first_name' => $fname,
            'last_name' => $lname,
            'company' => $company,
            'address_1' => '',
            'address_2' => '',
            'city' => '',
            'state' => '',
            'postcode' => '',
            'country' => '',
            'email' => $email,
            'phone' => ''
        ],
        'shipping' => [
            'first_name' => $fname,
            'last_name' => $lname,
            'company' => $company,
            'address_1' => '',
            'address_2' => '',
            'city' => '',
            'state' => '',
            'postcode' => '',
            'country' => ''
        ]
    ];
    array_push($newCustomers, $new_customer);
}

//var_dump($newCustomers);

$woocommerce = new Client(
    $store,
    $client_key,
    $secret_key,
    [
        'wp_api' => true,
        'version' => 'wc/v2',
    ]
);

foreach ($newCustomers as $customer) {
    try {
        $results = $woocommerce->post('customers', $customer);
        echo "<p>Customer ".$results->email." was created.</p>";

    } catch (HttpClientException $e) {
        echo "<p>".$e->getMessage()."</p>"; // Error message.
        $e->getRequest(); // Last request data.
        $e->getResponse(); // Last response data.
    }
}