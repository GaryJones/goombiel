                    <?php $has_thumbnail = ( ! has_post_thumbnail() || ( ! is_single() && ! yiw_get_option( 'show_featured_blog', 1 ) ) || ( get_post_type() == 'post' && is_single() && ! yiw_get_option( 'show_featured_single', 1 ) ) ) ? false : true; ?>
                       
                    <div id="post-<?php the_ID(); ?>" <?php post_class('hentry-post group blog-' . $GLOBALS['blog_type'] . ( ( ! $has_thumbnail ) ? ' without-thumbnail' : '' ) ); ?>>                
                              
                        <div class="thumbnail">
                            <?php if ( $has_thumbnail ) the_post_thumbnail( 'blog_small' ); ?>
                            <?php if( yiw_get_option( 'blog_show_date' ) ) : ?>
                            <p class="date">
                                <span class="month"><?php echo get_the_time('M') ?></span>
                                <span class="day"><?php echo get_the_time('d') ?></span>
                            </p>
                            <?php endif; ?>
                        </div>      
                            
                        <?php 
                            $link = get_permalink();
                            if ( is_single() )  the_title( "<h1 class=\"post-title\"><a href=\"$link\">", "</a></h1>" ); 
                            else                the_title( "<h2 class=\"post-title\"><a href=\"$link\">", "</a></h2>" ); 
                        ?>
                        
                        <div class="meta-bottom">
                        <?php if( yiw_get_option( 'blog_show_author' ) OR yiw_get_option( 'blog_show_categories' ) OR yiw_get_option( 'blog_show_comments' ) OR yiw_get_option( 'blog_show_socials' ) ) : ?>
                            <div class="meta group">
                                <?php if( yiw_get_option( 'blog_show_author' ) ) : ?><p class="author"><span><?php _e( 'by', 'yiw' ) ?> <?php the_author_posts_link() ?></span></p><?php endif; ?>
                                <?php if( yiw_get_option( 'blog_show_categories' ) ) : ?><p class="categories"><span><?php _e( 'In:', 'yiw' ); the_category( ', ' ) ?></span></p><?php endif; ?>
                                <?php if( yiw_get_option( 'blog_show_comments' ) ) : ?><p class="comments"><span><?php comments_popup_link(__('No comments', 'yiw'), __('1 comment', 'yiw'), __('% comments', 'yiw')); ?></span></p><?php endif; ?>
                                <?php if( yiw_get_option( 'blog_show_socials' ) ) : ?>
                                <p class="socials">
                                    <span><?php _e( 'Share on', 'yiw' ); ?></span> 
                                    <span>
                                        <a title="<?php _e( 'Share on Facebook', 'yiw' ); ?>" class="socials facebook-small" href="https://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>&t=<?php the_title() ?>">Facebook</a> 
                                        <a title="<?php _e( 'Share on Twitter', 'yiw' ); ?>" class="socials twitter-small" href="https://twitter.com/share?url=<?php echo the_permalink(); ?>&text==<?php the_title() ?>">Twitter</a> 
                                    </span>
                                </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                        <?php if ( is_single() ) : ?>
                        <div class="the-content"><?php the_content( str_replace( '->', '&rarr;', yiw_get_option('blog_read_more_text') ) ) ?></div>
                        <?php wp_link_pages(); ?>
                        <?php endif; ?>
                        
						<?php edit_post_link( __( 'Edit', 'yiw' ), '<p class="edit-link">', '</p>' ); ?>
					
						<?php if( is_single() ) the_tags( '<p class="list-tags">Tags: ', ', ', '</p>' ) ?>    
                    
                    </div>         