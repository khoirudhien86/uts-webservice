<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require "vendor\autoload.php";

use Restserver\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

class Login extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
    }

    public function index_post()
    {
        $this->db->where('email', $this->post('email'));
        $data = $this->db->get('users')->result();
        if (isset($data[0])) {
            if (password_verify($this->post('password'), $data[0]->password)) {
                $secret_key = base64_encode("gampang");
                $issuer_claim = "web service karyawan";
                $audience_claim = "Mohammad Khoirudin";
                $issuedat_claim = time();
                $notbefore_claim = $issuedat_claim + 10;
                $expire_claim = $issuedat_claim + 86400;
                $token = array(
                    "iss" => $issuer_claim,
                    "aud" => $audience_claim,
                    "iat" => $issuedat_claim,
                    "nbf" => $notbefore_claim,
                    "exp" => $expire_claim,
                    "data" => array(
                        "id" => $data[0]->id,
                        "nama_depan" => $data[0]->nama_depan,
                        "nama_belakang" => $data[0]->nama_belakang,
                        "email" => $data[0]->email
                    )
                );
                $jwt = JWT::encode($token, $secret_key);
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 200,
                    "message" => "Login successfully",
                    "token" => $jwt
                ];
                $this->response($result, 200);
            } else {
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 401,
                    "message" => "invalid password, login failed",
                    "token" => null
                ];
                $this->response($result, 401);
            }
        } else {
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 401,
                "message" => "invalid email, login failed",
                "token" => null
            ];
            $this->response($result, 401);
        }
    }
}
