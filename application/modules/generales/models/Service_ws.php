<?php

class Service_ws extends CI_Model {

    public function consumirWS($url, $post_array = false, $headersjson = false) {

        $headers = false;
        if ($headersjson) {
            $headers = array(
                'Content-Type:application/json',
//            'Authorization: Basic ' . base64_encode("user:password") // place your auth details here
            );
        }
        error_log(print_r($url, true));


        $handle = curl_init(); //your API url
        curl_setopt($handle, CURLOPT_URL, $url);
        if ($headers) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($handle, CURLOPT_HEADER, 1);
        }
//        curl_setopt($handle, CURLOPT_USERPWD, $username . ":" . $password);
//        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
//        curl_setopt($handle, CURLOPT_POST, 1);
//        curl_setopt($handle, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

        if ($post_array) {
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($post_array));
        }

        $response = curl_exec($handle);
//        error_log(print_r($response, true));
//        $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
//        $downloadLength = curl_getinfo($handle, CURLINFO_CONTENT_LENGTH_DOWNLOAD);        
        list($header, $body) = array_pad(explode("\r\n\r\n", $response, 2), 2, false);
        /*         * ********************************************************* */
        $headers = array();
//        error_log("..................");
        foreach (explode("\r\n", $header) as $i => $line) {
            if ($i === 0)
                $headers['http_code'] = $line;
            else {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }
        $headers;
        /*         * ********************************************************* */
//error_log("..................");
        if (curl_errno($handle)) {
            error_log($url);
            error_log(print_r(curl_error($handle), true));
//            print curl_error($handle);
            $respuesta = json_decode(json_encode(array("estado" => false, "mensaje" => print_r(curl_error($handle), true))));
        } else {
            $respuesta = json_decode(json_encode(array("estado" => true, "header" => $headers, "body" => $body)));
        }
        curl_close($handle);
        return $respuesta;
    }

}

?>