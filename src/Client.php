<?php
namespace sharinpix;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Signer\Hmac\Sha256 as JWTSigner;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request as HttpRequest;

class Client {
  private $url;

  private $host;
  private $api_path;
  private $secret_id;
  private $secret_secret;

  function __construct($url = null) {
    $this->url = $url;
    if(!$this->url != null && getenv('SHARINPIX_URL') != false){
      $this->url = getenv('SHARINPIX_URL');
    }
    if($this->url == null){
      throw new \Exception('No SharinPix credentials');
    }
    $this->parse_url();
  }

  public function parse_url(){
    $parsed = parse_url($this->url);
    $this->secret_id = $parsed['user'];
    $this->secret_secret = $parsed['pass'];
    $this->host = $parsed['host'];
    $this->api_path = $parsed['path'];
    if($this->api_path[-1] != '/'){
      $this->api_path .= '/';
    }
  }

  public function http_client(){
    return new HttpClient(['base_uri' => "https://$this->host$this->api_path"]);
  }

  public function import_url($album_id, $url){
    return $this->send_api_request($this->import_url_request($album_id, $url));
  }

  public function import_url_request($album_id, $url, $metadatas=[]){
    return $this->api_request(
      'POST',
      'imports',
      [
        'import_type' => 'url',
        'url' => $url,
        'album_id' => $album_id,
        'metadatas' => $metadatas
      ],
      [
        'abilities'=> [
          $album_id => [
            'Access'=> [
              'see' => true,
              'image_upload' => true
            ]
          ]
        ]
      ]
    );
  }

  public function call_api($type, $path, $body=null, $claims=null){
    if($claims == null){
      unset($claims);
    }
    return $this->send_api_request($this->api_request($type, $path, $body));
  }

  public function send_api_request($request){
    $response = $this->http_client()->send($request);
    if($response->getStatusCode() == 200 || $response->getStatusCode() == 201){
      return json_decode($response->getBody()->getContents());
    } else {
      throw 'SharinPix Error';
    }
  }

  public function api_request($type, $path, $body = null, $claims = ['admin'=>true]){
    $token = $this->token($claims);
    $headers = [
      'Authorization'=> "Token token=\"$token\""
    ];
    $body_encoded = null;
    if($body != null){
      $body_encoded = json_encode($body);
      $headers['Content-Type'] = 'application/json';
    }
    return new HttpRequest(
      $type,
      $path,
      $headers,
      $body_encoded
    );
  }

  public function token($payload = []){
    $jwt = new JWTBuilder();
    if(isset($payload['iss'])){
      $jwt->setIssuer(delete($payload['iss']));
    }else{
      $jwt->setIssuer($this->secret_id);
    }
    if(isset($payload['iat'])){
      $jwt->setIssuedAt(delete($payload['iat']));
    }else{
      $jwt->setIssuedAt(time());
    }
    if(isset($payload['exp'])){
      $jwt->setExpiration(delete($payload['exp']));
    }else{
      $jwt->setExpiration(time() + 3600);
    }
    foreach($payload as $key=>$value){
      $jwt->set($key, $value);
    }
    $jwt->sign(new JWTSigner(), $this->secret_secret);
    return $jwt->getToken();
  }

  public function url($path, $claims){
    return "https://$this->host/$path?token={$this->token($claims)}";
  }
}
