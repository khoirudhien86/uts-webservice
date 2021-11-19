<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Karyawan extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
    }

    //menampilkan data
    public function index_get()
    {
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
        } else {
            $this->db->where('id', $id);
            $karyawan = $this->db->get('karyawan')->result();
        }
        $result = [
            "took" => $_SERVER["REQUEST_TIME_FLOAT"],
            "code" => 200,
            "message" => "Response successfully",
            "data" => $karyawan,
        ];
        $this->response($result, 200);
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
