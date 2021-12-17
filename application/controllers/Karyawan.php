<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require "vendor\autoload.php";

use Restserver\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

class Karyawan extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    //menampilkan data
    public function index_get()
    {
        $authHeader = $this->input->get_request_header('Authorization');
        $arr = explode(" ", $authHeader);
        $jwt = isset($arr[1]) ? $arr[1] : "";
        $secretkey = base64_encode("gampang");

        if ($jwt) {
            $decoded = JWT::decode($jwt, $secretkey, array('HS256'));

            $id = $this->get('id');
            $karyawan = [];
            if ($id == '') {
                $data = $this->db->get('karyawan')->result();
                foreach ($data as $row => $key) :
                    $karyawan[] = [
                        "id" => $key->id,
                        "nama" => $key->nama,
                        "nik" => $key->nik,
                        "alamat" => $key->alamat,
                        "_links" => [(object)[
                            "href" => "keterangan\{$key->id_keterangan}",
                            "rel" => "keterangan",
                            "type" => "GET"
                        ]]
                    ];
                endforeach;
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 200,
                    "message" => "Response successfully",
                    "data" => $karyawan,
                ];
                $this->response($result, 200);
            } else {
                if (!is_numeric($id)) {
                    $result = [
                        "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                        "code" => 401,
                        "message" => "Data Yang Anda Cari Tidak Ditemukan!",
                        "data" => null,
                    ];
                } else {
                    $this->db->where('id', $id);
                    $data = $this->db->get('karyawan')->result();
                    foreach ($data as $row => $key) :
                        $karyawan[] = [
                            "id" => $key->id,
                            "nama" => $key->nama,
                            "nik" => $key->nik,
                            "alamat" => $key->alamat,
                            "_links" => [(object)[
                                "href" => "keterangan\{$key->id_keterangan}",
                                "rel" => "keterangan",
                                "type" => "GET"
                            ]]
                        ];
                    endforeach;

                    $etag = hash('sha256', $key->last_update);
                    $this->cache->save($etag, $karyawan, 300);
                    $this->output->set_header('Etag:' . $etag);
                    $this->output->set_header('Cache-Control: must-revalidate');
                    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
                        $this->output->set_header('HTTP/1.1 304 Not Modified');
                    } else {
                        $result = [
                            "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                            "code" => 200,
                            "message" => "Response successfully",
                            "data" => $karyawan,
                        ];
                    }
                }
                $this->response($result, 200);
            }
        } else {
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 401,
                "message" => "access denided",
                "token" => null
            ];
            $this->response($result, 401);
        }
    }

    //menambahkan data
    public function index_post()
    {
        $data = array(
            'id' => $this->post('id'),
            'nama' => $this->post('nama'),
            'nik' => $this->post('nik'),
            'alamat' => $this->post('alamat'),
            'id_keterangan' => $this->post('id_keterangan')
        );
        $this->db->where("id", $this->post('id'));
        $this->db->where("nik", $this->post('nik'));
        $cek = $this->db->get('karyawan')->num_rows();
        if ($cek == 0) {
            $insert = $this->db->insert('karyawan', $data);
            if ($insert) {
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 201,
                    "message" => "Data berhasil ditambahkan",
                    "data" => $data
                ];
                $this->response($result, 201);
            } else {
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 502,
                    "message" => "gagal menambahkan data",
                    "data" => null
                ];
                $this->response($result, 502);
            }
        } else {
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 304,
                "message" => "Data yang anda tambahkan sudah ada!",
                "data" => $data
            ];
            $this->response($result, 304);
        }
    }

    //memperbarui data yang telah ada
    public function index_put()
    {
        $id = $this->put('id');
        $data = array(
            'id' => $this->put('id'),
            'nama' => $this->put('nama'),
            'nik' => $this->put('nik'),
            'alamat' => $this->put('alamat'),
            'id_keterangan' => $this->put('id_keterangan')
        );
        $this->db->where('id', $id);
        $update = $this->db->update('karyawan', $data);
        if ($update) {
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 201,
                "message" => "Data berhasil diubah",
                "data" => $data
            ];
            $this->response($result, 200);
        } else {
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 502,
                "message" => "gagal mengubah data",
                "data" => null
            ];
            $this->response($result, 502);
        }
    }

    //menghapus data karyawan
    public function index_delete()
    {
        $id = $this->delete('id');
        $this->db->where('id', $id);
        $delete = $this->db->delete('karyawan');
        if ($delete) {
            $this->response(array('status' => 'berhasil menghapus data'), 201);
        } else {
            $this->response(array('status' => 'gagal menghapus data', 502));
        }
    }
}
