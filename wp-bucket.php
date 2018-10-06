<?php

/*
Plugin Name: WP Bucket
Description: Store uploads in S3
Author: Morais Junior
Version: 1.0.0
Author URI: https://github.com/geekdoagrest
*/

if (defined('S3_BUCKET')) {
	add_action( 'plugins_loaded', 's3_bucket_init' );

	// init and include files
	function s3_bucket_init() {
		if ( ! class_exists( '\\Aws\\S3\\S3Client' ) ) {
			require_once dirname( __FILE__ ) . '/lib/aws-sdk/aws-autoloader.php';
		}

		if ( ! class_exists( 'm2brimagem' ) ) {
			require_once dirname( __FILE__ ) . '/lib/m2brimagem.php';
		}	
	}

	// function tu upload
	function s3_bucket_upload($uri,$data,$type) {
		$params = array( 'version' => 'latest' );
		$params['credentials']['key']    = S3_BUCKET_KEY;
		$params['credentials']['secret'] = S3_BUCKET_SECRET;

		$params['signature'] = 'v4';
		$params['region']    = S3_BUCKET_REGION;

		$client = Aws\S3\S3Client::factory( $params );

		$result = $client->putObject(array(
		    'Bucket' => S3_BUCKET,
		    'Key'    => $uri,
		    'Body'   => $data,
		    'ContentType' => $type,
		    'Metadata'   => array(	    	
		        'Cache-Control' => 'public, max-age=31536000'
		    )		    
		));
	}

	// Upload image resizable
	function s3_bucket_upload_resizable($name,$data,$est,$size, $type) {
		$size = explode("x", $size);
		$temp = ABSPATH .'/wp-content/uploads/'.$name."-$size[0]x$size[1].".$est;

		file_put_contents($temp, file_get_contents($data));

		$oImg = new m2brimagem($temp);
		$oImg->redimensiona($size[0],$size[1],'crop');
		$oImg->grava($temp);

		s3_bucket_upload($name."-$size[0]x$size[1].".$est, file_get_contents($temp), $type);
		unlink($temp);		
	}

	// Hook to intercept upload
	add_filter('wp_handle_upload_prefilter', function ( $file ){
		$file['name'] = time().'-'.$file['name'];
	    s3_bucket_upload($file['name'],file_get_contents($file['tmp_name']), $file['type']);

	    if (defined('S3_BUCKET_SIZES')) {
	    	$name = explode(".", $file['name']);
	    	$est = $name[count($name) - 1];
	    	unset( $name[count($name) - 1] );
	    	$name = implode('.',$name);

	    	$sizes = explode(",", S3_BUCKET_SIZES);
	    	if(is_array($sizes) && !empty($sizes)){
	    		foreach ($sizes as $size) {
	    			s3_bucket_upload_resizable($name,$file['tmp_name'],$est, $size, $file['type']);
	    		}
	    	}
	    }

	    return $file;
	} );
}