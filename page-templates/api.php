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


$url = 'https://mars.excellent.ee:2888/api/1/invc?fields=code,name,group,uprice1,weight,uuid';
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
    $id = (string)$article->Code;
    $name = (string)$article->Name;
    $price = (string)$article->UPrice1;
    $category = (string)$article->Group;
    $weight = (string)$article->Weight;

    switch ($category) {
        case "JOON":
            $cat_id = 58;
            break;
        case "EHITU":
            $cat_id = 59;
            break;
    }

    $new_article = [
        'sku' => $id,
        'name' => $name,
        'type' => 'simple',
        'regular_price' => $price,
        'manage_stock'   => true,
        'stock_quantity' => 2,
        'in_stock' => true,
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
                'src' => 'http://localhost/wordpress/wp-content/uploads/2018/04/'.$id.'.jpg',
                'position' => 0
            ]
        ]
    ];
    array_push($newArticles, $new_article);
}

//var_dump($newArticles);
$upload_dir = wp_upload_dir();
echo $upload_dir['baseurl']."/product_img/";

$woocommerce = new Client(
    $store,
    $client_key,
    $secret_key,
    [
        'wp_api' => true,
        'version' => 'wc/v2',
    ]
);

//TOODETE LISAMINE
//foreach ($newArticles as $product) {
//    try {
//        $results = $woocommerce->post('products', $product);
//        echo "<p>Toode ".$results->name." loodi.</p>";
//
//    } catch (HttpClientException $e) {
//        echo "<p>".$e->getMessage()."</p>"; // Error message.
//        $e->getRequest(); // Last request data.
//        $e->getResponse(); // Last response data.
//    }
//}

//TOOTE UPDATE
//try {
//    $results_wp = $woocommerce->get('products');
//    foreach ($results_wp as $result) {
//        $wp_sku = $result->sku;
//        $wp_id = $result->id;
//        foreach ($newArticles as $product) {
//            if($wp_sku == $product['sku']) {
//                try {
//                    $results = $woocommerce->put('products/' . $wp_id, $product);
//                    echo "<p>Toode " . $results->sku . " muudeti.</p>";
//
//                } catch (HttpClientException $e) {
//                    echo "<p>" . $e->getMessage() . "</p>"; // Error message.
//                    $e->getRequest(); // Last request data.
//                    $e->getResponse(); // Last response data.
//                }
//            }
//        }
//    }
////    var_dump($results);
//
//} catch (HttpClientException $e) {
//    echo "<p>".$e->getMessage()."</p>"; // Error message.
//    $e->getRequest(); // Last request data.
//    $e->getResponse(); // Last response data.
//}
