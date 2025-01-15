<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Consultasapi extends CI_Controller {

    public function horaactual(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('content-type: application/json; charset=utf-8');

        $response = array();
        $now = new DateTime(null, new DateTimeZone('America/New_York'));
        $now->setTimezone(new DateTimeZone('America/Guayaquil'));    // Another way
        $response['datetime'] = $now->format("Y-m-d-H-i-sO");
        echo json_encode($response);
    }
}
?>