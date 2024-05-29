<?php

if ( ! class_exists( 'WDASS_HTML' ) ) {


class WDASS_HTML {
    public $premium_notice;

    public function __construct() {
        $this->premium_notice = '<span class="wdass__premium-notice">Unlock this feature by <a href="//webdevadvisor.com">Upgrading to premium version</a></span>';
    }
    
    
    /*-------------------------------------------
    *  Meta box tabs
    *-------------------------------------------*/
    public function tabs ( $args = [] ) {
        // $html = '';

        foreach ($args as $key => $value) {
            $class  = array_key_exists( 'class', $value ) ? $value['class'] : '';
            $id     = array_key_exists( 'id', $value ) ? $value['id'] : str_replace( " ", "-", strtolower($value['name']) );

            ?>
            <li class="wdass__meta-menu <?php echo esc_attr( $class ); ?>" data-menu="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $value['name'] ); ?></li>
            <?php
        }
    }
    
    
    /*-------------------------------------------
    *  Generating select input field
    *-------------------------------------------*/
    private function select ( $params ) {
        ?><select class="wdass_field <?php echo esc_attr( $params['class'] ); ?>" name="<?php echo esc_attr( $params['id'] ); ?>" <?php echo esc_attr( $params['disabled'] ); ?>><?php

        foreach ($params['args'] as $key => $val) {
            $selected = $params['value'] == $key ? 'selected="selected"' : '';
            ?><option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $val ); ?></option><?php
        }

        ?></select><?php
    }
    
    
    /*-------------------------------------------
    *  Radio input field
    *-------------------------------------------*/
    private function radio ( $params ) {
        foreach ($params['args'] as $key => $val) {
            $checked = $params['value'] == $key ? 'checked="checked"' : '';
            ?>
            <label><input
                class="wdass_field <?php echo esc_attr( $params['class'] ); ?>"
                type="radio"
                name="<?php echo esc_attr( $params['id'] ); ?>"
                value="<?php echo esc_attr( $key ); ?>"
                <?php echo esc_attr( $params['disabled'] ) . ' ' . esc_attr( $checked ); ?>
            /> <?php echo esc_html( $val ); ?></label><?php
        }
    }
    
    
    /*-------------------------------------------
    *  Checkbox input field
    *-------------------------------------------*/
    private function checkbox ( $params ) {
        foreach ($params['args'] as $key => $val) {
            $data = array_key_exists( 'data', $val) ? $val['data'] : [ 'yes', 'no' ];

            $cb_chekced  = $val['value'] == $data[0] ? 'checked="checked"' : '';
            $cb_label    = $val['label'];
            $cb_id       = 'wdass_' . $val['key'];
            $wrapper_id  = 'wdass_wrapper_' . $val['key'];
            $cb_value    = 'value="' . $val['value'] . '"';
            $cb_data     = 'data-on="' . $data[0] . '" data-off="' . $data[1] . '"';

            ?>
            <span class="<?php echo esc_attr( $wrapper_id ); ?>">
                <input
                class="wdass_field <?php echo esc_attr( $params['class'] ); ?>"
                type="checkbox"
                name="<?php echo esc_attr( $cb_id ); ?>"
                value="<?php echo esc_attr( $cb_value ); ?>"
                <?php echo esc_attr( $cb_chekced ) . ' ' . esc_attr( $cb_data ) . ' ' . esc_attr( $params['disabled'] ); ?>
            /> <label for="<?php echo esc_attr( $cb_id ); ?>"><?php echo esc_html( $cb_label ); ?></label>
            </span>
            <?php
        }
    }
    
    
    /*-------------------------------------------
    *  Default input field
    *-------------------------------------------*/
    public function input (  $params  ) {
        ?>
        <input
            class="wdass_field <?php echo esc_attr( $params['class'] ); ?>"
            type="<?php echo esc_attr($params['type']); ?>"
            name="<?php echo esc_attr($params['id']); ?>"
            placeholder="<?php echo esc_attr($params['placeholder']); ?>"
            value="<?php echo esc_attr($params['value']); ?>"
            <?php echo esc_attr($params['disabled']); ?>
        />
        <?php
    }
    
    
    /*-------------------------------------------
    *  Textarea input field
    *-------------------------------------------*/
    public function textarea ( $params ) {
        ?>
        <textarea
            class="wdass_field <?php echo esc_attr( $params['class'] ); ?>"
            name="<?php echo esc_attr($params['id']); ?>"
            placeholder="<?php echo esc_attr($params['placeholder']); ?>"
            <?php echo esc_attr($params['disabled']); ?>
        ><?php echo esc_textarea( $params['value'] ); ?></textarea>
        <?php
    }
    
    
    /*-------------------------------------------
    *  Meida field
    *-------------------------------------------*/
    public function media ( $params ) {
        $media_url = $params['media_id'] ? wp_get_attachment_url ( $params['media_id'] ) : WDASS_ROOT_URL . 'assets/images/placeholder.png';
        $premium = array_key_exists('field_class', $params) && $params['field_class'] == 'wdass__requres_premium' ? true : false;
        ?>
        <div class="wdass__media-field <?php echo $premium ? esc_attr( $params['field_class'] ) : ''; ?>" data-pid="<?php echo esc_attr( $params['post_id'] ); ?>">
            <a href="javascript:void(0)" class="wdass__add-media <?php echo esc_attr( $params['class'] ); ?>">
                <img src="<?php echo esc_attr( $media_url ); ?>" />
            </a>

            <a href="javascript:void(0)" class="wdass__remove-media" style="display: <?php echo esc_attr( $params['media_id'] ? 'block' : 'none' ); ?>;">Remove Attachment</a>

            <input class="wdass__media-input wdass_field" name="<?php echo esc_attr( 'wdass_' . $params['field_key'] ); ?>" type="hidden" value="<?php echo esc_attr( $params['media_id'] ); ?>" />

            <?php if ( $premium ) : ?>
                <span class="wdass__premium-notice">Unlock this feature by <a href="//webdevadvisor.com">Upgrading to premium version</a></span>
            <?php endif; ?>
        </div>
        <?php
    }
    
    
    /*-------------------------------------------
    *  Generating meta fields
    *-------------------------------------------*/
    public function field ( $params ) {
        $params['is_premium']  = array_key_exists( 'field_class', $params) && str_contains($params['field_class'], 'wdass__requres_premium') ? true : false;
        $params['field_class'] = array_key_exists( 'field_class', $params) ? $params['field_class'] : '';
        $params['class']       = array_key_exists( 'class', $params) ? $params['class'] : '';
        $params['id']          = 'wdass_' . $params['id'];
        $params['value']       = array_key_exists( 'value', $params ) ?  $params['value'] : '';
        $params['placeholder'] = array_key_exists( 'placeholder', $params) ? $params['placeholder'] : '';
        $params['disabled']    = $params['is_premium'] ? 'disabled="disabled"' : '';


        ?>
        <p class="form-field <?php echo esc_attr( $params['field_class'] ); ?>">
            <label>
                <strong><?php echo esc_html( $params['label'] ); ?></strong>
            </label>

            <?php

            switch ( $params['type'] ) {
                case 'select':
                    $this->select( $params );
                    break;

                case 'radio':
                    $this->radio( $params );
                    break;

                case 'checkbox':
                    $this->checkbox( $params );
                    break;

                case 'textarea':
                    $this->textarea( $params );
                    break;
                
                default:
                    $this->input( $params );
                    break;
            }

            if ( $params['is_premium'] ) : ?>
            <span class="wdass__premium-notice">Unlock this feature by <a href="//webdevadvisor.com">Upgrading to premium version</a></span>
            <?php endif; ?>

        </p>
        <?php
    }
}


}