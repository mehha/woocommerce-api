<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.2.0
 */

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="woocommerce-order">

	<?php if ( $order ) : ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'woocommerce' ) ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php _e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), $order ); ?></p>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

				<li class="woocommerce-order-overview__order order">
					<?php _e( 'Order number:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_order_number(); ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<?php _e( 'Date:', 'woocommerce' ); ?>
					<strong><?php echo wc_format_datetime( $order->get_date_created() ); ?></strong>
				</li>

				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
					<li class="woocommerce-order-overview__email email">
						<?php _e( 'Email:', 'woocommerce' ); ?>
						<strong><?php echo $order->get_billing_email(); ?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__total total">
					<?php _e( 'Total:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_formatted_order_total(); ?></strong>
				</li>

				<?php if ( $order->get_payment_method_title() ) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<?php _e( 'Payment method:', 'woocommerce' ); ?>
						<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
					</li>
				<?php endif; ?>

			</ul>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

        <?php

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        require __DIR__ . '/vendor/autoload.php';
        require 'config.php';
        require 'ArrayToXML.php';


        $woocommerce = new Client(
            $store,
            $client_key,
            $secret_key,
            [
                'wp_api' => true,
                'version' => 'wc/v2',
            ]
        );

        $order_number = $order->get_order_number();

//TELLIMUSE SAATMINE
        function post_order($woocommerce, $order_number){

            try {
                $results = $woocommerce->get('orders/'.$order_number);

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

        post_order($woocommerce, $order_number);

        ?>

	<?php else : ?>

		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), null ); ?></p>
	<?php endif; ?>

</div>
