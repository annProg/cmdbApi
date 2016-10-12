<?php
namespace iTopApi {
    class iTopClient {
        var $endpoint;
        var $user;
        var $password;
        var $debug = false;
        var $certificateCheck = true;

        public function __construct($endpoint,$user,$password,$version='1.0') {

            $this->endpoint = $endpoint;
            $this->user = $user;
            $this->password = $password;
            $this->version = $version;
        }

        function setCertificateCheck($bool) {
            $this->certificateCheck = $bool;
        }

        public function sendRequest(array $data) {
            $url = $this->endpoint . '/webservices/rest.php';
            $data['auth_user'] = $this->user;
            $data['auth_pwd'] = $this->password;
            $payload = json_encode($data);
            $query = array(
                'version' => $this->version,
                'auth_user' => $this->user,
                'auth_pwd' => $this->password,
                'json_data' => $payload
            );

            $params = http_build_query($query);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,$url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_POST, count($params));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            if( ! $this->certificateCheck ) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            } else {
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
            }
            $jsonResponse = curl_exec($curl);
            //$response = json_decode($jsonResponse,true);
            curl_close($curl);
            return $jsonResponse;
            /*
            if(!is_array($response))
                throw new \Exception('Invalid response from server : '.$jsonResponse);

            if($response['code'] != 0)
                throw new \Exception('iTop Exception : '.$response['message']);

            return $response;
             */
        }

        public function operation($operation, array $data=array()) {
            $data['operation'] = $operation;
            return $this->sendRequest($data);
        }

        public function coreGet($class,$query=null,$output="*") {
            if(is_null($query))
                $query = 'SELECT '.$class;
            return $this->operation('core/get',array(
                'class' => $class,
                'key' => $query,
                'output_fields' =>$output
            ));
        }

        public function coreDelete($class,$query,$comment=null) {
            if (is_null($comment))
                $comment = 'iTopAPI library delete '.$class.' from '.$this->user;
            return $this->operation('core/delete',array(
                'class' => $class,
                'key' => $query,
                'comment' => $comment
            ));
        }

        // soon to be deprecated :
        public function coreGetCustomSelect($class,$query) {
            return $this->coreGet($class,$query);
        }

        public function coreUpdate($class,$query,$data,$comment=null) {
            if (is_null($comment))
                $comment = 'iTopAPI library update '.$class.' from '.$this->user;

            return $this->operation('core/update',array(
                'class' => $class,
                'key' => $query,
                'fields' => $data,
                'comment' => $comment
            ));

        }

        public function coreCreate($class,$data,$comment=null) {
            if (is_null($comment))
                $comment = 'iTopAPI library create '.$class.' from '.$this->user;

            return $this->operation('core/create',array(
                'class' => $class,
                'fields' => $data,
                'comment' => $comment
            ));

        }
        public function coreRelated($class,$query) {
            return $this->operation('core/get_related',array(
                'redundancy' => "true",
                'key' => $query,
                'depth' => "5",
                'relation' => "impacts",
                'direction' => "down",
                'class' => $class,
            ));

        }

    }
}
