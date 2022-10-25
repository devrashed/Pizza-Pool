<?php
/**
 * Plugin Name: PizzaPOOL
 * Description: PizzaPOOL simple Delivery Plugin
 * Plugin URI: https://wooPool.net
 * Author: Rasheduzzaman Khan
 * Version: 1.0
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html 
 */



 /*
  *
  * Frontend 
  *
 */

 add_action( 'wp_footer', 'custom_script');

 function custom_script()
 {
    ?>

    <script>
        var close = document.getElementsByClassName("closebtn");
        var i;

        for (i = 0; i < close.length; i++) {
          close[i].onclick = function(){
            var div = this.parentElement;
            div.style.opacity = "0";
            setTimeout(function(){ div.style.display = "none"; }, 600);
        }
    }
</script>

<style>
    .alert {
      padding: 20px;
      background-color: #f44336;
      color: white;
      opacity: 1;
      transition: opacity 0.6s;
      margin-bottom: 15px;
      text-align:center;
    }

  .alert.success {background-color: #04AA6D;}
  .alert.info {background-color: #2196F3;}
  .alert.warning {background-color: #ff9800;}

  .closebtn {
      margin-left: 15px;
      color: white;
      font-weight: bold;
      float: right;
      font-size: 22px;
      line-height: 20px;
      cursor: pointer;
      transition: 0.3s;
  }

  .closebtn:hover {
      color: black;
  }
</style>

<?php 
}     

 /*
  *
  * Add to Cart Discount
  *
 */


add_action( 'woocommerce_cart_calculate_fees', 'discount_based_on_cart_total', 10, 1 );
function discount_based_on_cart_total( $cart_object ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    $cart_total = $cart_object->cart_contents_total; // Cart total

    $percent = 40;

    $discount = $cart_total * $percent / 100; 
    
    $cart_object->add_fee( "Discount ($percent%)", -$discount, true );
    
}


 
 /*
  *
  * Checkout
  *
 */

add_action( 'woocommerce_review_order_before_order_total', 'get_dine_checkbox_field', 20 );
function get_dine_checkbox_field(){
    echo '<tr class="packing-select"><th>';

    woocommerce_form_field( 'dinein', array(
        'type'          => 'checkbox',
        'class'         => array('form-row-wide'),
        'label'         => __('Dine In(10%)'),
        'placeholder'   => __(''),
    ), WC()->session->get('dinein') ? '1' : '' );

    echo '</th><td>';
}

// jQuery - Ajax script
add_action( 'wp_footer', 'checkout_fee_script' );
function checkout_fee_script() {
    // Only on Checkout
    if( is_checkout() && ! is_wc_endpoint_url() ) :

        if( WC()->session->__isset('dinein') )
            WC()->session->__unset('dinein')
        ?>
        <script type="text/javascript">
            jQuery( function($){
                if (typeof wc_checkout_params === 'undefined')
                    return false;

                $('form.checkout').on('change', 'input[name=dinein]', function(){
                    var fee = $(this).prop('checked') === true ? '1' : '';

                    $.ajax({
                        type: 'POST',
                        url: wc_checkout_params.ajax_url,
                        data: {
                            'action': 'dinein',
                            'dinein': fee,
                        },
                        success: function (result) {
                            $('body').trigger('update_checkout');
                        },
                    });
                });
            });
        </script>
        <?php
    endif;
}

// Get Ajax request and saving to WC session
//add_action( 'wp_ajax_dinein', 'get_dinein' );
add_action( 'wp_ajax_nopriv_dinein', 'get_dinein' );
function get_dinein() {
    if ( isset($_POST['dinein']) ) {
        WC()->session->set('dinein', ($_POST['dinein'] ? true : false) );
    }
    die();
}


// Add a custom calculated fee conditionally
add_action( 'woocommerce_cart_calculate_fees', 'set_dinein' );
function set_dinein( $cart_object ){

    if ( is_admin() && ! defined('DOING_AJAX') || ! is_checkout() )
        return;

    if ( 1 == WC()->session->get('dinein') ) {
     $items_count = WC()->cart->get_cart_contents_count();
     $cart_total = $cart_object->cart_contents_total;
     
     $percent = 10; 
     $fee_amount = $cart_total * $percent / 100; 

     $cart_object->add_fee( "Dine In ($percent%)", +$fee_amount, true );
 }
}



add_filter( 'woocommerce_form_field' , 'remove_optional_txt_from_installment_checkbox', 10, 4 );
function remove_optional_txt_from_installment_checkbox( $field, $key, $args, $value ) {
    // Only on checkout page for Order notes field
    if( 'dinein' === $key && is_checkout() ) {
        $optional = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
        $field = str_replace( $optional, '', $field );
    }
    return $field;
}

 /*
  *
  * Schedule 
  *
 */

///  $days = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];

// 16:00 - 22:00 -- 4pm-10pm,12pm-10pm,,  && date('H') >= 16 && date('H') <= 22

add_action('wp_head', 'display_message_header');

function display_message_header(){

 $close = "<div class='alert'>
  <span class='closebtn'>&times;</span>  
    No Delivery & No Takeway
</div>";

$success = "<div class='alert success'>
  <span class='closebtn'>&times;</span>  
    We are Open To Recive The order, 
</div>";

    if(date('l') === 'Saturday') {

      echo $close;

  }elseif(date('l') === 'Sunday') {

       echo $close;

  }elseif(date('l') === 'Monday') {

       echo $close;

  }elseif(date('l')=== 'Tuesday') {

     echo $close;

  }elseif(date('l') === 'Wednesday' && date('G') >= 16 && date('G') <= 22 ) {

       echo $success;

  }elseif ( date('l') === 'Thursday' && date('G') >= 12 && date('G') <= 22 ){

      echo $success;

  }elseif( date('l') === 'Friday' && date('G') >= 12 && date('G') <= 22 ) {

      echo $success;

  }else{

 echo $close;

 }

}
