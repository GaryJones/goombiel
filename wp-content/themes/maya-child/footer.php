                <div class="clear"></div>
                <?php 
                    $type = yiw_get_option( 'footer_type', 'normal' ); 
                    if( strpos($type, "big") !== false )
                        get_template_part('footer','big');
                ?>
                
                <!-- START FOOTER -->
                <div id="copyright" class="group">
                    
                    <div class="inner group">
                        <ul class="social-media">
                            <li><a href=""><i class="fa fa-facebook-square"></i></a></li>
                            <li><a href=""><i class="fa fa-instagram"></i></a></li>
                        
                        </ul>
                        <?php if( $type == 'normal' || $type == 'big-normal' ) : ?>
                        <div class="left">
                            <?php yiw_convertTags( yiw_addp( stripslashes( __( yiw_get_option( 'copyright_text_left', 'Copyright <a href="%site_url%"><strong>%name_site%</strong></a>' ), 'yiw' ) ) ) ) ?> <?php echo date("Y"); ?>
                        </div>
                        <?php elseif( $type == 'centered' || $type == 'big-centered' ) : ?> 
                        <div class="center">
                            <?php yiw_convertTags( yiw_addp( stripslashes( __( yiw_get_option( 'footer_text_centered' ), 'yiw' ) ) ) ) ?>  
                        </div>
                    <?php endif ?>
                    </div>
                </div>
                <!-- END FOOTER -->     
            </div>     
            <!-- END WRAPPER -->        
        </div>     
        <!-- END SHADOW WRAPPER -->     
    
    <?php wp_footer(); ?>
    <!-- scripts -->
    <script>
    jQuery(document).ready(function($) {
        <?php 
            //functions.php 
            showCartButton(); 
            showMyAccountButton();
        ?>

        if(cartCount > 0){
            
            $('.cart-button, .checkout-button').css('display', 'inline-block');

        }
        if(LoggedIn == true){
            $('.my-account-button, .wishlist-button').css('display', 'inline-block');
        }

        cartIcon = '<i class="fa fa-shopping-cart"></i> ';
        $(cartIcon).prependTo('.topbar-level-1 li:first-child > a');

    });
    </script>
    </body>
</html>