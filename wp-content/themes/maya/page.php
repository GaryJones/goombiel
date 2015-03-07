<?php        

/**

 * @package WordPress

 * @subpackage YIW Themes

 * @since 1.0

 */                        

error_reporting(0);

get_header() ?>                        

        

		<div id="primary" class="layout-<?php echo yiw_layout_page() ?>">    

		    <div class="inner group">

                <?php get_template_part('slogan') ?>

    			

    			<?php get_template_part( 'accordion-slider' ) ?>  

    			

                <!-- START CONTENT -->

                <div id="content" class="group">

                    <?php if( yiw_get_option( '_show_breadcrumbs_page' ) == 'yes' ) yiw_breadcrumb(); ?>

                    

                    <?php get_template_part( 'loop', 'page' ) ?> 

                    

                    <?php comments_template() ?>

                </div>

                <!-- END CONTENT -->

                

                <!-- START SIDEBAR -->

                <?php get_sidebar() ?>

                <!-- END SIDEBAR -->    

                                  

                <!-- START EXTRA CONTENT -->

        		<?php get_template_part( 'extra-content' ) ?>      

                <!-- END EXTRA CONTENT -->

            </div>

        </div>       

        

<?php get_footer() ?>