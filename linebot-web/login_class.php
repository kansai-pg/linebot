<?php
#https://qiita.com/kakakaori830/items/4d54d012ff4ebf10c173
ini_set('display_errors', 1);

class get_from_line {
    private $code;

    public function __construct($code) {
        $this->code = $code;
    }

    public function get_access_token() {
        $postData = array(
            'grant_type'    => 'authorization_code',
            'code'          => $this->code,
            'redirect_uri'  => 'https://linebot.japan-is.fun/view.php',
            'client_id'     => '1656389284',
            'client_secret' => 'cfab8fa27e86da4405bc9187370f87a6'
          );

          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
          curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/oauth2/v2.1/token');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $response = curl_exec($ch);
          $httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
          curl_close($ch);
          
          $json = json_decode($response);

          if ($httpcode === 400){
            echo "authorization codeエラー不正リクエスト";
            exit(1);

          } else {
            return $json->access_token;
          }

    }

    public function get_user_info(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->get_access_token()));
        curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/profile');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        return $this->infos = json_decode($response);
    }
}
?>
