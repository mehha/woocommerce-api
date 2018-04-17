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


$url = 'https://mars.excellent.ee:2888/api/1/invc?fields=code,name,group,uprice1,weight';
$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Basic " . base64_encode("$username:$password"),
        'timeout' => 2.0
    )
));
$data = file_get_contents($url, false, $context);
$raw_data = new SimpleXMLElement($data);

$newArticles = array();

foreach ($raw_data->INVc as $article) {
    $code = (integer)$article->Code;
    $name = (string)$article->Name;
    $price = (string)$article->UPrice1;
    $category = (string)$article->Group;
    $weight = (integer)$article->Weight;

    switch ($category) {
        case "JOON":
            $cat_id = 58;
            break;
        case "EHITU":
            $cat_id = 59;
            break;
    }

    $new_article = [
        'name' => $name,
        'type' => 'simple',
        'regular_price' => $price,
        'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
        'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
        'weight' => $weight,
        'categories' => [
            [
                'id' => $cat_id
            ]
        ],
        'images' => [
            [
                'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg',
                'position' => 0
            ]
        ]
    ];
    array_push($newArticles, $new_article);
}

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

foreach ($newArticles as $product) {
    try {
        $results = $woocommerce->post('products', $product);
        echo "<p>Toode lisati.</p>";

    } catch (HttpClientException $e) {
        echo "<p>".$e->getMessage()."</p>"; // Error message.
        $e->getRequest(); // Last request data.
        $e->getResponse(); // Last response data.
    }
}