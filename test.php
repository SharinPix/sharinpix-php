<?php
require('vendor/autoload.php');
use sharinpix\Client as SharinpixClient;

$client = new SharinpixClient();
$id = 'super_album_test';
echo $client->url(
  "pagelayout/$id",
  array(
    'abilities'=>array(
      $id=>array(
        'Access'=> array(
          'see'=> true,
          'image_list'=> true,
          'image_upload'=> true,
        )
      )
    )
  )
) . "\n";
var_dump($client->call_api('GET', "albums/$id"));
var_dump($client->call_api('GET', "albums/$id/images"));
var_dump($client->import_url($id, 'http://res.cloudinary.com/demo/image/upload/w_150,h_150,c_thumb,g_face,r_20,e_sepia/l_cloudinary_icon,g_south_east,x_5,y_5,w_50,o_60,e_brightness:200/a_10/front_face.png'));
echo "\n";
