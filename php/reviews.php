<?php

class Reviews extends TimberSite {

    function __construct() {
        add_filter( 'timber_context', [$this, 'add_to_context'] );
        parent::__construct();
      }

    function add_to_context( $context ) {
        $context["reviews"] = $this->fetchReviews();
        return $context;
    }

    private function fetchReviews(){
        $response = wp_remote_get("https://www.local-marketing-reports.com/external/showcase-reviews/widgets/e3cc7e8f525200dd6caa3a816db0ab8740dcf5ce");
        if ( is_array( $response ) && !is_wp_error( $response ) ) {
            $headers = $response['headers']; 
            $body    = json_decode($response['body']);
            // $body = $body->results;
            return $body;
        } else {
           $body = json_decode("{}");
           return $body;
        }
    }
}
new Reviews();