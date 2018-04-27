<?php
function understrap_remove_scripts() {
    wp_dequeue_style( 'understrap-styles' );
    wp_deregister_style( 'understrap-styles' );

    wp_dequeue_script( 'understrap-scripts' );
    wp_deregister_script( 'understrap-scripts' );

    // Removes the parent themes stylesheet and scripts from inc/enqueue.php
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {

	// Get the theme data
	$the_theme = wp_get_theme();
    wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . '/css/child-theme.min.css', array(), $the_theme->get( 'Version' ) );
    wp_enqueue_script( 'jquery');
	wp_enqueue_script( 'popper-scripts', get_template_directory_uri() . '/js/popper.min.js', array(), false);
    wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $the_theme->get( 'Version' ), true );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

/* add custom js to wp keeping in mind jQuery loads in �no conflict� mode */
function madis_cf7_custom() {
    ?>
    <script type="text/javascript">
        jQuery(function($){
            var start = $('.date-holte input');
            var end = $('.time-holte input');
            var hours = (new Date().getHours());

            start.on('change', function() {
                var date = $(this).datepicker('getDate');
                var dayOfWeek = date.getUTCDay();
                end.timepicker('option', 'stepMinute', 30);
                end.timepicker('option', 'hourMin', 4);

                switch (dayOfWeek) {
                    case 0:
                        console.log("Mon");
                        end.timepicker('option', 'hourMax', 19);
                        break;
                    case 1:
                        console.log("Tue");
                        end.datepicker('option', 'hourMax', 19);
                        break;
                    case 2:
                        console.log("Wed");
                        end.timepicker('option', 'hourMax', 19);
                        break;
                    case 3:
                        console.log("Thu");
                        end.timepicker('option', 'hourMax', 19);
                        break;
                    case 4:
                        console.log("Fri");
                        end.timepicker('option', 'hourMax', 20);
                        break;
                    case 5:
                        console.log("Sat");
                        end.timepicker('option', 'hourMax', 20);
                        break;
                    case 6:
                        console.log("Sun");
                        end.timepicker('option', 'hourMax', 19);
                }

                // start_date.setDate(start_date.getDate() + 3);
                // console.log(start_date.getDate());
                // end.datepicker('option', 'minDate', start_date);
            });
        });
    </script>
    <?php
}
add_action( 'wp_footer', 'madis_cf7_custom', 99999 );

//add_action('woocommerce_thankyou', 'enroll_student', 10, 1);
function enroll_student( $order_id ) {

    if ( ! $order_id )
        return;

    // Getting an instance of the order object
    $order = wc_get_order( $order_id );
    echo "<p>madis_test</p>";

    if($order->is_paid())
        $paid = 'yes';
    else
        $paid = 'no';

    // iterating through each order items (getting product ID and the product object)
    // (work for simple and variable products)

    // Displaying something
    echo '<p>Order ID: '. $order_id . ' — Order Status: ' . $order->get_status() . ' — Order is paid: ' . $paid . '</p>';
}
