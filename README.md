## Introduction
WP Bucket is a simple plugin for keeping files in AWS S3, it sends a copy of the file along with the redimensions indicated in the configuration.

## Configuration in wp_config.php
define( 'S3_BUCKET', 'backet_name' );
define( 'S3_BUCKET_KEY', '...' );
define( 'S3_BUCKET_SECRET', '...' );
define( 'S3_BUCKET_REGION', 'sa-east-1' );

define( 'S3_BUCKET_SIZES', '434x149,100x200' );

##  Permissoins
Log into AWS.
Under Services select IAM.
Select Users > [Your User]
Open Permissoins Tab
Attach the **AmazonS3FullAccess** policy to the account