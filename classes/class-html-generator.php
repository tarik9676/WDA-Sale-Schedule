<?php

if ( ! class_exists( 'WDASS_HTML' ) ) {


class WDASS_HTML {
    public $premium_notice;

    public function __construct() {
        $this->premium_notice = '<span class="wdass__premium-notice">Unlock this feature by <a href="//webdevadvisor.com">Upgrading to premium version</a></span>';
    }

    public function tabs ( $args = [] ) {
        $html = '';

        foreach ($args as $key => $value) {
            $html .= '<li class="wdass__meta-menu {CLASS}" data-menu="{ID}">{NAME}</li>';

            $html = strtr( $html, [
                '{CLASS}'   => array_key_exists( 'class', $value ) ? esc_attr( $value['class'] ) : '',
                '{ID}'      => array_key_exists( 'id', $value ) ? esc_attr( $value['id'] ) : str_replace( " ", "-", strtolower($value['name']) ),
                '{NAME}'    => $value['name']
            ] );
        }

        echo $html;
    }

    private function select ( $params ) {
        $input = "";

        $input .= '<select class="wdass_field {CLASS}" name="{ID}" {DISABLED}>';

        foreach ($params['args'] as $key => $val) {
            $selected = $params['value'] == $key ? 'selected="selected"' : '';
            $input .= '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
        }

        $input .= "</select>";

        return $input;
    }

    private function radio ( $params ) {
        $input = "";

        foreach ($params['args'] as $key => $val) {
            $checked = $params['value'] == $key ? 'checked="checked"' : '';
            $input .= '<label><input class="wdass_field {CLASS}" type="radio" name="{ID}" ' . $checked . ' value="' . $key . '" {DISABLED}/> ' . $val . '</label>';
        }

        return $input;
    }

    private function checkbox ( $params ) {
        $input = "";

        foreach ($params['args'] as $key => $val) {
            $data = array_key_exists( 'data', $val) ? $val['data'] : [ 'yes', 'no' ];

            $input .= '<span class="{WRAPPER_ID}"><input class="wdass_field {CLASS}" type="checkbox" name="{CB_ID}" {CB_VALUE} {CB_CHECKED} {CB_DATA} {DISABLED}/> <label for="{CB_ID}">{CB_LABEL}</label></span>';


            /*----- String Translation -----*/
            $input = strtr( $input, [
                '{CB_CHECKED}'  => $val['value'] == $data[0] ? 'checked="checked"' : '',
                '{CB_LABEL}'    => $val['label'],
                '{CB_ID}'       => 'wdass_' . $val['key'],
                '{WRAPPER_ID}'  => 'wdass_wrapper_' . $val['key'],
                '{CB_VALUE}'    => 'value="' . $val['value'] . '"',
                '{CB_DATA}'     => 'data-on="' . $data[0] . '" data-off="' . $data[1] . '"',
            ] );
        }

        return $input;
    }

    public function field ( $params ) {
        $label = '<label><strong>{LABEL}</strong></label>';
        // $label = '<strong>{LABEL}</strong>';
		$input = '';

        $premium_notice = '<span class="wdass__premium-notice">Unlock this feature by <a href="//webdevadvisor.com">Upgrading to premium version</a></span>';

        $premium = array_key_exists( 'field_class', $params) && str_contains($params['field_class'], 'wdass__requres_premium') ? $premium_notice : false;

		switch ( $params['type'] ) {
            case 'select':
                $input = $this->select( $params );
                break;

			case 'radio':
                $input = $this->radio( $params );
				break;

            case 'checkbox':
                $input = $this->checkbox( $params );
                break;

            case 'textarea':
                $input = '<textarea class="wdass_field {CLASS}" name="{ID}" {PLACEHOLDER} {DISABLED}>{VALUE}</textarea>';
                break;
			
			default:
				$input = '<input class="wdass_field {CLASS}" type="{TYPE}" name="{ID}" value="{VALUE}" {PLACEHOLDER} {DISABLED}/>';
				break;
		}

        $html = '<p class="form-field {FIELD_CLASS}">' . $label . $input . $premium . '</p>';


		/*----- String Translation -----*/
        $html = strtr( $html, [
            '{FIELD_CLASS}' => array_key_exists( 'field_class', $params) ? esc_attr( $params['field_class'] ) : '',
            '{CLASS}'       => array_key_exists( 'class', $params) ? esc_attr( $params['class'] ) : '',
            '{ID}'          => 'wdass_' . $params['id'],
            '{LABEL}'       => $params['label'],
            '{TYPE}'        => esc_attr( $params['type'] ),
            '{DISABLED}'    => $premium ? 'disabled' : '',
            '{VALUE}'       => array_key_exists( 'value', $params ) ? esc_attr( $params['value'] ) : '',
            '{PLACEHOLDER}' => array_key_exists( 'placeholder', $params) ? 'placeholder="' . esc_attr( $params['placeholder'] ) . '"' : '',
        ] );

        echo $html;
    }

    public function media ( $params ) {
        $media_url = $params['media_id'] ? wp_get_attachment_url ( $params['media_id'] ) : WDASS_ROOT_URL . 'assets/images/placeholder.png';
        $premium = array_key_exists('field_class', $params) && $params['field_class'] == 'wdass__requres_premium' ? true : false;
        ?>
        <div class="wdass__media-field <?php echo $premium ? $params['field_class'] : ''; ?>" data-pid="<?php echo $params['post_id']; ?>">
            <a href="javascript:void(0)" class="wdass__add-media <?php echo $params['class']; ?>">
                <img src="<?php echo $media_url; ?>" />
            </a>

            <a href="javascript:void(0)" class="wdass__remove-media" style="display: <?php echo $params['media_id'] ? 'block' : 'none'; ?>;">Remove Attachment</a>

            <input class="wdass__media-input wdass_field" name="<?php echo 'wdass_' . $params['field_key']; ?>" type="hidden" value="<?php echo esc_attr( $params['media_id'] ); ?>" />
            <?php
            echo $premium ? '<span class="wdass__premium-notice">Unlock this feature by <a href="//webdevadvisor.com">Upgrading to premium version</a></span>' : '';
            ?>
        </div>
        <?php
    }
}


}