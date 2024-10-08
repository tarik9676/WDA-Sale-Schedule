<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wdass_meta_boxes' ) ) :
    
    /*------------------------------------------------------
    *  Calling the class on the post edit screen.
    *------------------------------------------------------*/
    function wdass_meta_boxes () {
        new WDASS__meta_boxes();
    }

    
    /*------------------------------------------------------
    *  Calling meta box caller only for admin
    *------------------------------------------------------*/
    add_action('admin_init', function() {
        add_action( 'load-post.php', 'wdass_meta_boxes' );
    });

endif;


if ( ! class_exists( 'WDASS__meta_boxes' ) ) :

class WDASS__meta_boxes extends WDASS_HTML {

    
    /*------------------------------------------------------
    *  Pre assigning event object
    *------------------------------------------------------*/
    private $event_fields = [];

    
    /*------------------------------------------------------
    *  $meta_object carries meta key & value 
    *------------------------------------------------------*/
    private $meta_object = [];

    
    /*------------------------------------------------------
    *  Pre defining fields / placeholder
    *------------------------------------------------------*/
    private $pre_defined_fields = [
        'post_title'        => '404',
        'post_status'       => 'publish',
        'post_name'         => '404',
        'post_content'      => '404',
        'post_excerpt'      => '404',
        'comment_status'    => 'open',
        '_virtual'          => '404',
        '_downloadable'     => '404',
        '_manage_stock'     => '404',
        '_stock_status'     => 'instock',
        '_stock'            => '404',
        '_thumbnail_id'     => '404',
        '_regular_price'    => '404',
        '_sale_price'       => '404',
        '_sku'              => '404'
    ];

    
    /*------------------------------------------------------
    *  Pre defining existing event object
    *------------------------------------------------------*/
    private $existent = [
        'id'                => 0,
        'schedule_status'   => 'inactive',
        'schedule_date'    => '',
        'schedule_time'    => ''
    ];
    
    /*-------------------------------------------
    *  Hook into the appropriate actions when
    *  the class is constructed.
    *-------------------------------------------*/
    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post_product', [ $this, 'collect_data' ], 10, 3 );
    }


    /*-------------------------------------------
    *  Adding the meta box container.
    *-------------------------------------------*/
    public function add_meta_box( $post_type ) {
        if ( in_array( $post_type, ['product'] ) ) {
            add_meta_box(
                '_wdass_event_data',
                __( 'Schedule Event Data', WDASS_TEXTDOMAIN ), 
                [ $this, 'meta_box_html' ],
                $post_type,
                'normal',
                'high'
            );
        }
    }


    /*-------------------------------------------
    *  Meta Box HTML
    *-------------------------------------------*/
    public function meta_box_html( $post ) {
        global $wpdb;
        $table_events       = $wpdb->prefix . 'wdass_events';
        $table_eventmeta    = $wpdb->prefix . 'wdass_eventmeta';
        

        /*----- Nonce Field : So that we can verify while saving -----*/

		wp_nonce_field( 'wdass_meta_auth', 'wdass_meta_auth_nonce' );
        

        /*----- Pre assinging parent data -----*/

        $parent_empty_data = [];
        $parent_empty_data[ $post->ID ] = $this->pre_defined_fields;
        $parent_empty_data[ $post->ID ]['terms'] = '404';
        

        /*------------------------------------------------
        *  Getting product object by post id
        *------------------------------------------------*/
        $product = wc_get_product( $post->ID );
            
            

        /*----------------------------------------------------
        *  Getting meta_key & content columns
        *----------------------------------------------------*/
        $cache_key_eventmeta = 'wdass_eventmeta_cache';
        $all_eventmeta = wp_cache_get( $cache_key_eventmeta );

        if ($all_eventmeta === false) {
            $all_eventmeta = $wpdb->get_results($wpdb->prepare(
                "SELECT m.post_id, m.meta_key, m.content
                FROM %i as e
                LEFT JOIN %i AS m
                ON e.id = m.event_id
                WHERE e.object_id = %d AND m.type = %s;",
                [ $table_events, $table_eventmeta, $post->ID, 'modified' ]
            ));

            wp_cache_set($cache_key_eventmeta, $all_eventmeta);
        }

        /*----------------------------------------------------
        *  get_values() stores organized data to meta_object
        *  which helps val() to distribute
        *-----------------------------------------------------*/
        $this->get_values( $all_eventmeta );
            
        

        /*----------------------------------------------------------------
        *  Storing existing event id, status & time to existent object
        *----------------------------------------------------------------*/
        $cache_key_allevents = 'wdass_allevents_cache';
        $allevents = wp_cache_get( $cache_key_allevents );

        if ($allevents === false) {
            $allevents = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM %i
                WHERE object_id = %d;",
                [ $table_events, $post->ID ]
            ));

            wp_cache_set($cache_key_allevents, $allevents);
        }
        

        if ( count($allevents) ) {
            $this->existent['id']               = $allevents[0]->id;
            $this->existent['schedule_status']  = $allevents[0]->schedule_status;
            $this->existent['schedule_date']    = $allevents[0]->schedule_date;
            $this->existent['schedule_time']    = $allevents[0]->schedule_time;
            
            /*----- Also adding tags & categories if an event exists -----*/
            $parent_empty_data[ $post->ID ]['terms'] = $this->val($post->ID, 'terms');
        }

        ?>

        <ul class="sidebar">
            <?php
            /*------------------------------------------------
            *  Meta Box Tabs
            *------------------------------------------------*/
            $this->tabs([
                ['name' => 'Date-Time & Status', 'id' => 'schedule-settings'],
                ['name' => 'General', 'id' => 'general', 'class' => 'active'],
                ['name' => 'Inventory', 'id' => 'inventory'],
                ['name' => 'Contents', 'id' => 'contents'],
                ['name' => 'Variations', 'id' => 'variations', 'class' => 'show_if_variable'],
                ['name' => 'Miscellaneous', 'id' => 'misc'],
            ]);

            ?>
        </ul>
        <div class="wdass__meta-container">


            <!-- BASIC SCHEDULE SETTINGS | Schedule Switch, Schedule Time & Date -->
            <div class="wdass__meta-content hide" data-container="schedule-settings">
                <?php

                $this->field([
                    'label' => 'Schedule Status',
                    'type'  => 'radio',
                    'value' => $this->existent['schedule_status'],
                    'id'    => 'schedule_status',
                    'args'  => [
                        'pending'   => 'Active',
                        'inactive'  => 'Inactive',
                        'completed' => 'Completed',
                    ]
                ]);

                echo '<hr class="wdass__spacer">';
                
                ?>
                <p class="form-field ">
                    <label><strong>Schedule Date-Time</strong></label>
                    <input class="wdass_field " type="date" name="wdass_schedule_date" id="wdass_schedule_date" value="<?php echo esc_attr( $this->existent['schedule_date'] ); ?>" />
                    <input class="wdass_field" type="time" name="wdass_schedule_time" id="wdass_schedule_time" value="<?php echo esc_attr( $this->existent['schedule_time'] ); ?>" />
                </p>
                
                <hr class="wdass__spacer">
                <?php


                $this->field([
                    'label' => 'Restore Original Data',
                    'type'  => 'radio',
                    'value' => 'no_restore',
                    'id'    => 'restore_status',
                    'args'  => [
                        'no_restore'    => 'No',
                        'restore_now'   => 'Restore Now',
                        'restore_later' => 'Restore Later',
                    ],
                    'field_class' => 'wdass__requres_premium'
                ]);

                echo '<hr class="wdass__spacer">';


                /*------------------------------------------------
                *  Restore Date-Time for each products
                *------------------------------------------------*/
                ?>
                <p class="form-field wdass__requres_premium">
                    <label><strong>Restore Date-Time</strong></label>
                    <input class="wdass_field " type="date" name="wdass_restore_date" id="wdass_restore_date" value="" disabled />
                    <input class="wdass_field" type="time" name="wdass_restore_time" id="wdass_restore_time" value="" disabled />
                    <?php $this->premium_notice(); ?>
                </p>
            </div>
            

            <!-- PRODUCT BASIC/USUAL FIELDS | Title, Thumbnail, Status, Regular & Sale price -->
            <div class="wdass__meta-content active" data-container="general">
                <?php

                
                /*------------------------------------------------
                *  Product Status
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Product Status',
                    'type'  => 'select',
                    'value' => $this->val($post->ID, 'post_status'),
                    'id'    => 'post_status',
                    'class' => 'wdass__parent-input',
                    'args'  => [
                        'publish'   => 'Publish',
                        'draft'     => 'Draft',
                        'trash'     => 'trash',
                        'pending'   => 'Pending Review',
                    ],
                ]);

                echo '<hr class="wdass__spacer">';

                /*------------------------------------------------
                *  Post Title
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Product Title',
                    'type'  => 'text',
                    'value' => $this->val($post->ID, 'post_title'),
                    'id'    => 'post_title',
                    'class' => 'wdass__parent-input',
                ]);

                echo '<hr class="wdass__spacer">';
                
                
                /*------------------------------------------------
                *  Product Regular Price
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Regular Price',
                    'type'  => 'text',
                    'value' => $this->val($post->ID, '_regular_price'),
                    'id'    => '_regular_price',
                    'class' => 'wdass__parent-input',
                    'field_class' => 'show_if_simple show_if_grouped show_if_external',
                ]);

                echo '<hr class="wdass__spacer wdass_hide show_if_simple show_if_grouped show_if_external">';
                
                
                /*------------------------------------------------
                *  Product Sale Price
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Sale Price',
                    'type'  => 'text',
                    'value' => $this->val($post->ID, '_sale_price'),
                    'id'    => '_sale_price',
                    'class' => 'wdass__parent-input',
                    'field_class' => 'show_if_simple show_if_grouped show_if_external'
                ]);

                echo '<hr class="wdass__spacer show_if_simple show_if_grouped show_if_external">';

                
                /*------------------------------------------------
                *  Product thumbnail
                *------------------------------------------------*/
                $this->media([
                    'post_id'   => $post->ID,
                    'field_key' => '_thumbnail_id',
                    'media_id'  => $this->val($post->ID, '_thumbnail_id'),
                    'class'     => 'wdass__parent-input'
                ]);

                echo '<hr class="wdass__devider">';

                ?>

                <h4>Categories</h4>

                <?php
                $product_categories = [];
                $product_tags = [];

                if ( is_array($parent_empty_data[ $post->ID ]['terms']) ) {
                    $product_categories = $parent_empty_data[ $post->ID ]['terms']['product_cat'];
                    $product_tags = $parent_empty_data[ $post->ID ]['terms']['product_tag'];
                } else {
                    $product_categories = wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'ids'));
                    $product_tags = wp_get_post_terms($post->ID, 'product_tag', array('fields' => 'ids'));
                }

                // Retrieve all available product categories
                $all_product_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                ));

                // Output the HTML for category selection
                ?>
                <ul id="wdass--category-list">
                    
                <?php foreach ($all_product_categories as $category) : ?>
                    <li class='wdass--category'>
                        <label>
                            <input
                                type="checkbox"
                                data-id="<?php echo esc_attr($category->term_id); ?>"
                                value=""
                                <?php checked(in_array($category->term_id, $product_categories), true); ?>
                            />
                            <?php echo esc_html($category->name); ?>
                        </label>
                    </li>
                <?php endforeach; ?>

                </ul>
                
                <hr class="wdass__spacer">
                
                <h4>Tags</h4>
                <?php

                // Retrieve all available product tags
                $all_product_tags = get_terms(array(
                    'taxonomy' => 'product_tag',
                    'hide_empty' => false,
                ));

                if ( count( $all_product_tags ) ) {
                    ?>
                    <ul id="wdass--tag-list">

                    <?php foreach ($all_product_tags as $tag) : ?>
                        <li class='wdass--tag'>
                            <label>
                                <input
                                    type="checkbox"
                                    data-id="<?php echo esc_attr( $tag->term_id ); ?>"
                                    value=""
                                    <?php checked(in_array($tag->term_id, $product_tags), true); ?>
                                    />
                                <?php echo esc_html( $tag->name ); ?>
                            </label>
                        </li>
                    <?php endforeach; ?>

                    </ul>
                    
                    <?php
                }
                ?>
            </div>


            <!-- PARENT PRODUCT INVENTORY | SKU, Stock Management, Stock Qty, Stock Status -->
            <div class="wdass__meta-content hide" data-container="inventory">
                <?php
                
                
                /*------------------------------------------------
                *  Product SKU
                *------------------------------------------------*/
                $this->field([
                    'label' => 'SKU',
                    'type'  => 'text',
                    'value' => $this->val($post->ID, '_sku'),
                    'id'    => '_sku',
                    'class' => 'wdass__parent-input'
                ]);

                echo '<hr class="wdass__spacer">';
                

                /*------------------------------------------------
                *  Stock Management Switch
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Stock Management',
                    'type'  => 'checkbox',
                    'id'    => $post->ID,
                    'class' => 'wdass__parent-input',
                    'args'  => [
                        [
                            'key' => '_manage_stock',
                            'label' => 'Manage Stock?',
                            'checked' => $this->val($post->ID, '_manage_stock') == 'yes' ? 'checked' : '',
                            'value' => $this->val($post->ID, '_manage_stock') == 'yes' ? 'yes' : 'no'
                        ]
                    ],
                    'field_class' => 'show_if_simple show_if_grouped show_if_external'
                ]);
                
                
                /*------------------------------------------------
                *  Stock Quantity
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Stock Quantity',
                    'type'  => 'number',
                    'value' => $this->val($post->ID, '_stock'),
                    'id'    => '_stock',
                    'class' => 'wdass__parent-input',
                    'field_class' => $this->val($post->ID, '_manage_stock') == 'yes' ? '' : 'wdass_hide',
                ]);

                echo '<hr class="wdass__spacer">';
                
                
                /*------------------------------------------------
                *  Stock Status Switch
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Stock Status',
                    'type'  => 'radio',
                    'value' => $this->val($post->ID, '_stock_status'),
                    'id'    => '_stock_status',
                    'args'  => [
                        'instock'       => 'In Stock',
                        'outofstock'    => 'Out of Stock',
                        'onbackorder'   => 'On Backorder'
                    ],
                ]);
                ?>
            </div>

            
            <!-- PARENT CONTENTS | Long & Short Description -->
            <div class="wdass__meta-content hide" data-container="contents">
                <h3>Long Description</h3>
                <?php
                
                
                /*------------------------------------------------
                *  Product Long Description
                *------------------------------------------------*/
                $description_editor_settings = array(
                    'media_buttons' => true,
                    'textarea_name' => 'wdass_' . $post->ID . '_post_content',
                    'textarea_rows' => 30,
                    'quicktags' => true
                );
                $long_editor_content = $this->val($post->ID, 'post_content') ? $this->val($post->ID, 'post_content') : '';
                wp_editor( $long_editor_content, 'wdass_' . $post->ID . '_post_content', $description_editor_settings );

                echo "<br /><br />";

                ?>
                <h3>Short Description</h3> <?php
                
                
                /*------------------------------------------------
                *  Product Short Description (Excerpt)
                *------------------------------------------------*/
                $short_editor_settings = array(
                    'media_buttons' => true,
                    'textarea_name' => 'wdass_' . $post->ID . '_post_excerpt',
                    'textarea_rows' => get_option('default_post_edit_rows', 30),
                    'quicktags' => true,
                );
                $short_editor_content = $this->val($post->ID, 'post_excerpt') ? $this->val($post->ID, 'post_excerpt') : '';
                wp_editor( $short_editor_content, 'wdass_' . $post->ID . '_post_excerpt', $short_editor_settings );

                ?>
            </div>

            
            <!-- PRODUCT VARIABLES -->
            <?php
            /*------------------------------------------------
            *  Variable Items Data For Variable Products
            *  If current product is a variable Type
            *------------------------------------------------*/
            if ( $product->is_type( 'variable' ) ) :
                ?>
                <div class="wdass__meta-content hide" data-container="variations">

                    <ul class="wdass__variations">
                    <?php
                
                
                    /*------------------------------------------------
                    *  Getting Variation Data & Looping Through
                    *------------------------------------------------*/
                    $variations = $product->get_available_variations();
                    
                    foreach ( $variations as $key => $variation ) {
                        $var_id = $variation['variation_id'];

                        $parent_empty_data[ $var_id ] = $this->pre_defined_fields;

                        $var_title = '<strong>#' . $var_id . "</strong> " . $post->post_title . " - ";
                        $var_title .= ucwords(implode( ', ', array_values($variation['attributes']) ));

                        ?>
                        <li data-id="<?php echo $var_id; ?>">

                            <!-- Variation/Tab Title -->
                            <div class="wdass__variation-title">
                                <strong><?php echo $var_title; ?></strong>
                            </div>

                            
                            <div class="wdass__variation-container hide">
                                
                                <div class="wdass__row">

                                <?php
                                /*------------------------------------------------
                                *  Variable Thumbnail
                                *------------------------------------------------*/
                                $this->media([
                                    'post_id'   => $var_id,
                                    'field_key' => '_thumbnail_id',
                                    'media_id'  => $this->val($var_id, '_thumbnail_id'),
                                    'class'     => 'wdass__variable-input'
                                ]);
                                

                                /*------------------------------------------------
                                *  Variable SKU
                                *------------------------------------------------*/
                                $this->field([
                                    'label' => 'SKU',
                                    'type'  => 'text',
                                    'value' => $this->val($var_id, '_sku'),
                                    'id'    => '_sku',
                                    'class' => 'wdass__variable-input'
                                ]);
                                ?>

                                </div> <!-- WDA ROW ENDS HERE -->

                                
                                <div class="wdass__row">

                                <?php
                                /*------------------------------------------------
                                *  Variable Options
                                *------------------------------------------------*/
                                $this->field([
                                    'class' => 'wdass__variable-input',
                                    'label' => 'Options',
                                    'type' => 'checkbox',
                                    'value' => 'yes',
                                    'id' => $var_id,
                                    'args'  => [
                                        [
                                            'key'       => 'post_status',
                                            'label'     => 'Enabled',
                                            'checked'   => $this->val($var_id, 'post_status') == 'publish' ? 'checked' : '',
                                            'value'     => $this->val($var_id, 'post_status') ? $this->val($var_id, 'post_status') : 'publish',
                                            'data'      => ['publish', 'private']
                                        ],
                                        [
                                            'key'       => '_virtual',
                                            'label'     => 'Virtual',
                                            'checked'   => $this->val($var_id, '_virtual') == 'yes' ? 'checked' : '',
                                            'value'     => $this->val($var_id, '_virtual') ? $this->val($var_id, '_virtual') : 'no',
                                            'data'      => ['yes', 'no']
                                        ],
                                        [
                                            'key' => '_downloadable',
                                            'label' => 'Downloadable',
                                            'checked'   => $this->val($var_id, '_downloadable') == 'yes' ? 'checked' : '',
                                            'value'     => $this->val($var_id, '_downloadable') ? $this->val($var_id, '_downloadable') : 'no',
                                            'data'      => ['yes', 'no']
                                        ],
                                        [
                                            'key' => '_manage_stock',
                                            'label' => 'Manage Stock?',
                                            'checked'   => $this->val($var_id, '_manage_stock') == 'yes' ? 'checked' : '',
                                            'value'     => $this->val($var_id, '_manage_stock') ? $this->val($var_id, '_manage_stock') : 'no',
                                            'data'      => ['yes', 'no']
                                        ],
                                    ],
                                ]);
                                ?>

                                </div> <!-- WDA ROW ENDS HERE -->
                                

                                <div class="wdass__row">

                                <?php
                                /*------------------------------------------------
                                *  Variable Regular Price
                                *------------------------------------------------*/
                                $this->field([
                                    'label' => 'Regular Price',
                                    'type'  => 'text',
                                    'value' => $this->val($var_id, '_regular_price'),
                                    'id'    => '_regular_price',
                                    'class' => 'wdass__variable-input'
                                ]);


                                /*------------------------------------------------
                                *  Variable Sale Price
                                *------------------------------------------------*/
                                $this->field([
                                    'label' => 'Sale Price',
                                    'type'  => 'text',
                                    'value' => $this->val($var_id, '_sale_price'),
                                    'id'    => '_sale_price',
                                    'class' => 'wdass__variable-input'
                                ]);
                                ?>

                                </div> <!-- WDA ROW ENDS HERE -->

                                
                                <div class="wdass__row">
                                    
                                <?php
                                /*------------------------------------------------
                                *  Stock Status
                                *------------------------------------------------*/
                                $this->field([
                                    'label' => 'Stock Status',
                                    'class' => 'wdass_field',
                                    'type' => 'select',
                                    'value' => $this->val($var_id, '_stock_status'),
                                    'id' => '_stock_status',
                                    'args'  => [
                                        'instock'       => 'In Stock',
                                        'outofstock'    => 'Out of Stock',
                                        'onbackorder'   => 'On Backorder'
                                    ],
                                ]);


                                /*------------------------------------------------
                                *  Stock Quantity
                                *------------------------------------------------*/
                                $this->field([
                                    'label' => 'Stock Quantity',
                                    'class' => 'wdass_field',
                                    'type' => 'number',
                                    'value' => $this->val($var_id, '_stock'),
                                    'id' => '_stock',
                                    'field_class' => $this->val($var_id, '_manage_stock') == 'yes' ? '' : 'wdass_hide',
                                ]);
                                ?>

                                </div> <!-- WDA ROW ENDS HERE -->
                                
                                <div class="wdass__row">
                                <?php
                                $this->field([
                                    'label' => 'Description',
                                    'type' => 'textarea',
                                    'value' => $this->val($var_id, 'post_content'),
                                    'id' => 'post_content'
                                ]);
                                ?>
                                </div>
                            </div> <!-- Single variation container ends here -->

                        </li> <!-- The li tag ends here -->
                        
                        <?php
                    }
                    
                    
                    ?>
                    </ul> <!-- The ul tag ends here -->
                </div>
            <?php endif; ?>

            
            <!-- MISCELLANEOUS | Slug, Comment Status -->
            <div class="wdass__meta-content hide" data-container="misc">

                <?php
                /*------------------------------------------------
                *  Product Slug
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Slug',
                    'type'  => 'text',
                    'value' => $this->val($post->ID, 'post_name'),
                    'id'    => 'post_name',
                    'class' => 'wdass__parent-input'
                ]);
                

                echo '<hr class="wdass__spacer">';


                /*------------------------------------------------
                *  Comment/Review Status Switch
                *------------------------------------------------*/
                $this->field([
                    'label' => 'Review Status',
                    'type' => 'radio',
                    'value' => $this->val($post->ID, 'comment_status'),
                    'id' => 'comment_status',
                    'class' => 'wdass__parent-input',
                    'args'  => [
                        'open'      => 'Enabled',
                        'closed'    => 'Disabled',
                    ]
                ]);
                ?>

            </div>

        </div>

        <!-- The hidden input which stores parent product modified data as JSON string -->

        
        <!-------------------------------------------------------------------
        *  This hidden input stores modified data as JSON string
        -------------------------------------------------------------------->
        <input
            type="hidden"
            id="wdass_parent_data"
            name="wdass_parent_data"
            value='<?php echo wp_json_encode( $parent_empty_data ); ?>'
        />


        <!-------------------------------------------------------------------
        *  This hidden input stores existing event data as JSON string
        -------------------------------------------------------------------->
        <input
            type="hidden"
            name="wdass_existing_event"
            id="wdass_existing_event"
            value='<?php echo wp_json_encode( $this->existent ); ?>'
        />
        <?php
    }


    /*-------------------------------------------
    *  Collect Meta Field Data
    *-------------------------------------------*/

    public function collect_data ( $post_ID, $post, $update ) {

        /*-------------------------------------------
        *  Bailout if it's an auto save
        *-------------------------------------------*/

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_ID;
        }


        /*-------------------------------------------
        *  Also bailout if current user doesn't
        *  have enough permission
        *-------------------------------------------*/

        if ( ! current_user_can( 'manage_options' ) ) {
            return $post_ID;
        }
        
 
        /*-------------------------------------------
        *  Bailout if nonce is not verified
        *-------------------------------------------*/

        if (
            ! isset( $_POST['wdass_meta_auth_nonce'] ) ||
            ! wp_verify_nonce( $_POST['wdass_meta_auth_nonce'], 'wdass_meta_auth' )
        ) { return $post_ID; }

        if ( $_SERVER["REQUEST_METHOD"] == "POST" && $update ) {
            $this->event_fields['schedule_status'] = !empty( $_POST['wdass_schedule_status'] ) ? sanitize_text_field( $_POST['wdass_schedule_status'] ) : 'inactive';

            if ( $this->event_fields['schedule_status'] == 'inactive' ) {
                return $post_ID;
            }

            $this->event_fields['restore_status'] = !empty( $_POST['wdass_restore_status'] ) ? sanitize_text_field( $_POST['wdass_restore_status'] ) : 'no_restore';
            
            $this->event_fields['schedule_date'] = sanitize_text_field( $_POST['wdass_schedule_date'] );
            $this->event_fields['schedule_time'] = sanitize_text_field( $_POST['wdass_schedule_time'] );
            
            $this->event_fields['restore_date'] = sanitize_text_field( $_POST['wdass_restore_date'] );
            $this->event_fields['restore_time'] = sanitize_text_field( $_POST['wdass_restore_time'] );
            
            $existent = json_decode( stripslashes( $_POST['wdass_existing_event'] ), true );
            
            global $wpdb;

            $table_events       = $wpdb->prefix . 'wdass_events';
            $table_eventmeta    = $wpdb->prefix . 'wdass_eventmeta';

            $event_data = [];
            $event_data['modified'] = [];
            $event_data['original'] = [];


            /*-------------------------------------------
            *  Getting modified data to be scheduled
            *-------------------------------------------*/
            $parent_modified_data = json_decode( stripslashes( $_POST['wdass_parent_data'] ), true );

            $event_data[ 'modified' ] = $parent_modified_data;
            $event_data[ 'modified' ][ $post_ID ][ 'post_excerpt' ] = !empty($_POST['wdass_' . $post_ID . '_post_excerpt']) ? stripslashes($_POST['wdass_' . $post_ID . '_post_excerpt']) : '404';
            $event_data[ 'modified' ][ $post_ID ][ 'post_content' ] = !empty($_POST['wdass_' . $post_ID . '_post_content']) ? stripslashes($_POST['wdass_' . $post_ID . '_post_content']) : '404';


            /*-------------------------------------------
            *  If no existing event 
            *-------------------------------------------*/

            if ( !$existent['id'] ) {


                /*-------------------------------------------
                *  Getting generic parent data
                *-------------------------------------------*/
                $parent_post_data = [
                    'post_title'    => $post->post_title,
                    'post_name'     => $post->post_name,
                    'post_content'  => $post->post_content,
                    'post_excerpt'  => $post->post_excerpt,
                    'post_status'   => $post->post_status,
                    'comment_status'=> $post->comment_status
                ];
                

                /*-------------------------------------------
                *  Getting parent meta data
                *-------------------------------------------*/
                $parent_post_meta = get_post_meta( $post_ID );
                
                $parent_meta_data = [
                    '_thumbnail_id' => isset( $parent_post_meta['_thumbnail_id'][0] )   ? $parent_post_meta['_thumbnail_id'][0] : '',
                    '_regular_price'=> isset( $parent_post_meta['_regular_price'][0] )  ? $parent_post_meta['_regular_price'][0] : '',
                    '_sale_price'   => isset( $parent_post_meta['_sale_price'][0] )     ? $parent_post_meta['_sale_price'][0] : '',
                    '_sku'          => isset( $parent_post_meta['_sku'][0] )            ? $parent_post_meta['_sku'][0] : '',
                    '_manage_stock' => isset( $parent_post_meta['_manage_stock'][0] )   ? $parent_post_meta['_manage_stock'][0] : '',
                    '_stock'        => isset( $parent_post_meta['_stock'][0] )          ? $parent_post_meta['_stock'][0] : '',
                    '_stock_status' => isset( $parent_post_meta['_stock_status'][0] )   ? $parent_post_meta['_stock_status'][0] : ''
                ];


                /*-----------------------------------------------------
                *  Adding generic & meta data together
                *  And assigning to entire_data > original
                *-----------------------------------------------------*/
                $event_data[ 'original' ][ $post_ID ] = array_merge( $parent_post_data, $parent_meta_data );

                $product_categories = wp_get_post_terms($post_ID, 'product_cat', array('fields' => 'ids'));
                $product_tags = wp_get_post_terms($post_ID, 'product_tag', array('fields' => 'ids'));

                $event_data[ 'original' ][ $post_ID ][ 'terms' ] = json_encode([
                    'product_cat' => $product_categories,
                    'product_tag' => $product_tags
                ]);
                
                



                /*-----------------------------------------------------
                *  >>> STARTING : VARIABLE SECTION
                *-----------------------------------------------------*/
                $variations_modified_object = $parent_modified_data;
                unset( $variations_modified_object[ $post_ID ] );


                /*---------------------------------------------
                *  Get original variation data if has any
                *----------------------------------------------*/
                if ( count( $variations_modified_object ) ) {
                    foreach ( $variations_modified_object as $var_id => $var_obj ) {
                        $variable_post_data = get_post ( $var_id, ARRAY_A );
                        $variable_meta_data = get_post_meta( $var_id );

                        $this_variation_obj = [];

                        foreach ( $var_obj as $var_key => $var_val ) {
                            $this_variation_obj[ $var_key ] = $variable_post_data[ $var_key ];
                            $this_variation_obj[ $var_key ] = $variable_meta_data[ $var_key ][ 0 ];
                        }

                        $event_data[ 'original' ][ $var_id ] = $this_variation_obj;
                    }
                }
                /*-----------------------------------------------------
                *  ### ENDING : VARIABLE SECTION
                *-----------------------------------------------------*/






                /*-----------------------------------------------------
                *  Create new event
                *-----------------------------------------------------*/

                $wpdb->insert(
                    $table_events,
                    [
                        'object_id'         => $post_ID,
                        'schedule_status'   => $this->event_fields['schedule_status'],
                        'schedule_date'     => $this->event_fields['schedule_date'],
                        'schedule_time'     => $this->event_fields['schedule_time'],
                        'restore_status'    => $this->event_fields['restore_status'],
                        'restore_date'      => $this->event_fields['restore_date'],
                        'restore_time'      => $this->event_fields['restore_time']
                    ],
                    [
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ]
                );
        
                $last_event_id = $wpdb->insert_id;


                /*-----------------------------------------------------
                *  Also save new meta modified data
                *-----------------------------------------------------*/
                $this->save_group( $wpdb, $last_event_id, 'modified', $event_data['modified'] );
                $this->save_group( $wpdb, $last_event_id, 'original', $event_data['original'] );
            } else {
                
                /*-----------------------------------------------------
                *  Only update if event already exists
                *-----------------------------------------------------*/

                $wpdb->update(
                    $table_events,
                    [
                        'schedule_status'   => $this->event_fields['schedule_status'],
                        'schedule_date'     => $this->event_fields['schedule_date'],
                        'schedule_time'     => $this->event_fields['schedule_time'],
                        'restore_status'    => $this->event_fields['restore_status'],
                        'restore_date'      => $this->event_fields['restore_date'],
                        'restore_time'      => $this->event_fields['restore_time']
                    ],
                    [ 'object_id' => $post_ID ],
                    [ '%s', '%s', '%s', '%s', '%s', '%s' ],
                    [ '%d' ]
                );
                
                $this->update_modified_data( $wpdb, $existent['id'], $event_data['modified'] );
            } // Ends if no existing event
        } else {
            return;
        }
    }


    /*----------------------------------------------------
    *  Distributing each product's data to insert
    *----------------------------------------------------*/
    private function save_group ( $wpdb, $event_id, $type, $data ) {
        foreach ( $data as $post_id => $post_data ) {
            $this->insert_single_post( $wpdb, $event_id, $post_id, $type, $post_data );
        }
    }


    /*-------------------------------------------
    *  Adding each new meta to DB
    *-------------------------------------------*/
    private function insert_single_post ( $wpdb, $event_id, $post_id, $type, $data ) {
        foreach ( $data as $meta_key => $value ) {
            $wpdb->insert(
                $wpdb->prefix . 'wdass_eventmeta',
                [
                    'event_id'  => $event_id,
                    'post_id'   => $post_id,
                    'type'      => $type,
                    'meta_key'  => $meta_key,
                    'content'   => empty($value) ? '404' : $value
                ],
                [ '%d', '%d', '%s', '%s', '%s' ]
            );
        }
    }


    /*----------------------------------------------------
    *  Distributing each product's data to update
    *----------------------------------------------------*/
    private function update_modified_data ( $wpdb, $event_id, $modified_data ) {
        foreach ( $modified_data as $post_id => $post_data ) {
            $this->update_modified_meta( $wpdb, $event_id, $post_id, $post_data );
        }
    }


    /*----------------------------------------------------
    *  Updating each meta fields
    *----------------------------------------------------*/
    private function update_modified_meta ( $wpdb, $event_id, $post_id, $post_data ) {
        foreach ( $post_data as $meta_key => $value ) {

            if ( $value !== '404' ) {
                $wpdb->update(
                    $wpdb->prefix . 'wdass_eventmeta',
                    [ 'content' => empty($value) ? '404' : $value ],
                    [
                        'event_id'  => $event_id,
                        'post_id'   => $post_id,
                        'type'      => 'modified',
                        'meta_key'  => $meta_key
                    ],
                    [ '%s' ],
                    [ '%d', '%d', '%s', '%s' ]
                );
            } else {
                continue;
            }

        }
    }


    /*------------------------------------------------------
    *  get_values() searches one meta field from meta_object
    *------------------------------------------------------*/
    private function get_values ( $sql_data ) {
        $results = [];

        foreach ( $sql_data as $key => $value ) {
            $results[ $value->post_id ][ $value->meta_key ] = $value->content;
        }

        $this->meta_object = $results;
    }


    /*------------------------------------------------------
    *  val() searches one meta field from meta_object
    *------------------------------------------------------*/
    private function val ( $id, $key ) {
        if ( array_key_exists( $id, $this->meta_object ) ) {
            return $this->meta_object[$id][$key] == '404' || empty( $this->meta_object[$id][$key] ) ? '' : $this->meta_object[$id][$key];
        }
    }

}

endif;