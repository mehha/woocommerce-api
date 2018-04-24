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


//$url = 'https://mars.excellent.ee:2888/api/1/invc?fields=code,name,group,uprice1,weight,uuid';
//$context = stream_context_create(array(
//    'http' => array(
//        'header'  => "Authorization: Basic " . base64_encode("$username:$password"),
//        'timeout' => 2.0
//    )
//));
//$data = file_get_contents($url, false, $context);
//$raw_data = new SimpleXMLElement($data);
//
//$newArticles = array();

//foreach ($raw_data->INVc as $article) {
//    $id = (string)$article->Code;
//    $name = (string)$article->Name;
//    $price = (string)$article->UPrice1;
//    $category = (string)$article->Group;
//    $weight = (string)$article->Weight;
//
//    switch ($category) {
//        case "JOON":
//            $cat_id = 58;
//            break;
//        case "EHITU":
//            $cat_id = 59;
//            break;
//    }
//
//    $new_article = [
//        'sku' => $id,
//        'name' => $name,
//        'type' => 'simple',
//        'regular_price' => $price,
//        'manage_stock'   => true,
//        'stock_quantity' => 2,
//        'in_stock' => true,
//        'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
//        'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
//        'weight' => $weight,
//        'categories' => [
//            [
//                'id' => $cat_id
//            ]
//        ],
//        'images' => [
//            [
//                'src' => 'http://localhost/wordpress/wp-content/uploads/product_img/'.$id.'.jpg',
//                'position' => 0
//            ]
//        ]
//    ];
//    array_push($newArticles, $new_article);
//}


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

//            var_dump($results);

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

            $order_items_one = [
                'OrdDate' => substr($results->date_created, 0, -9),
                'CustRegNr1' => '12815090',
                'Addr0' => $results->billing->first_name,
                'Addr1' => $results->billing->address_1,
                'Addr2' => $results->billing->address_1,
                'OurContact' => $results->billing->first_name,
                'CustContact' => $results->billing->first_name,
                'CustCat' => 'EES',
                'Objects' => 'ESIMEN,TEIEN',
                'OrderClass' => 'WWW',
                'Sum1' => 357.10,
                'Sum3' => 71.42,
                'Sum4' => $results->total,
                'Lang' => 'EST'
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
//            print $xml->buildXML($new_order);
            $parsed_xml = $xml->buildXML($new_order);

            $request_xml = "
            <data>
                <register>
                    <record>
                        <head>
                            <OrdDate>24.04.2018</OrdDate>
                            <CustRegNr1>12815090</CustRegNr1>
                            <Addr0>Linna Kohvik OÜ</Addr0>
                            <Addr1>Kivi tee 12</Addr1>
                            <Addr2>Pärnu 11225</Addr2>
                            <OurContact>Annika Ainus</OurContact>
                            <CustContact>Merike Kaunis</CustContact>
                            <CustCat>EES</CustCat>
                            <Objects>ESIMEN,TEIEN</Objects>
                            <OrderClass>WWW</OrderClass>
                            <Sum1>357.10</Sum1>
                            <Sum3>71.42</Sum3>
                            <Sum4>428.50</Sum4>
                            <Lang>EST</Lang>
                        </head>
                        <rows>
                            <row>
                                <ArtCode>036</ArtCode>
                                <Quant>20</Quant>
                                <Price>7.06</Price>
                                <Spec>Kohviuba \"Super\" 500 g</Spec>
                                <Sum>280.00</Sum>
                            </row>
                            <row>
                                <ArtCode>9</ArtCode>
                                <Quant>120</Quant>
                                <Price>7.06</Price>
                                <Spec>Uba \"Super\" 500 g</Spec>
                                <Sum>1280.00</Sum>
                            </row>
                        </rows>
                    </record>
                </register>
            </data>
            ";

//            print $request_xml;

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

        } catch (HttpClientException $e) {
            echo "<p>".$e->getMessage()."</p>"; // Error message.
            $e->getRequest(); // Last request data.
            $e->getResponse(); // Last response data.
        }
    }

post_order($woocommerce);
