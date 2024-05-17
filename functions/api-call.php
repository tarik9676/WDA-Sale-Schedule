<?php

if ( ! function_exists( 'wdass_api_call' ) ) {
    function wdass_api_call ( $endpoint_url ) {
        $curl = curl_init();

        curl_setopt( $curl, CURLOPT_URL, $endpoint_url );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

        $response = curl_exec( $curl );

        curl_close( $curl );

        return curl_error( $curl ) ? curl_error( $curl ) : json_decode( $response );
    }
}

if ( ! function_exists('wdass_execute_key') ) {
    function wdass_execute_key ( $code ) {
        // return array_sum(str_split(substr( $code, 4 ))) == array_sum(str_split(substr( 'LWDA507285', 4 ))) ? true : false;
        return $code == 'LWDA630495' ? true : false;
    }
}