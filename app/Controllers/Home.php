<?php

namespace App\Controllers;

class Home extends BaseController
{
    public $user_model;
    public $output = [
        'sukses'    => false,
        'pesan'     => '',
        'data'      => []
    ];

    public function __construct()
    {
        $this->user_model = new \App\Models\User_model();
    }

    public function index()
    {
        return view('home');
    }

    public function tambah()
    {
        $user_model = $this->user_model;
        if ($this->request->isAJAX()) {

            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'nama_user' => [
                    'label' => 'Nama User',
                    'rules' => 'required|is_unique[users.nama_user]',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} tidak boleh ada yang sama, silahkan coba yang lain'
                    ]
                ]
              
            ]);

            if (!$valid) {
                $msg = [
                    'error' => [
                        'namauser' => $validation->getError('nama_user'),
                    ]
                ];
            } else {
                $data = [
                    'nama_user' => $this->request->getVar('nama_user'),
                    'alamat'    => $this->request->getVar('alamat')
                ];

                // $this->user->insert($data);
                $user_model->tambah($data);
                $msg = [
                    'sukses' => 'Data user berhasil tersimpan'
                ];
            }
            echo json_encode($msg);
        }else {
            exit('Maaf tidak dapat diproses');
        }
    }

    public function edit()
    {
        $user_model = $this->user_model;
        if ($this->request->isAJAX()) {
            $id_user = $this->request->getVar('id_user');
            $result = $user_model->edit($id_user);
            if ($result) {
                $this->output['sukses'] = true;
                $this->output['pesan']  = 'Data ditemukan';
                $this->output['data']   = $result;
            }

            echo json_encode($this->output);
        }
    }

    public function update()
    {
        $user_model = $this->user_model;
        if ($this->request->isAJAX()) {
            $data = [
                'nama_user' => $this->request->getVar('nama_user'),
                'alamat'    => $this->request->getVar('alamat')
            ];
            $id_user = $this->request->getVar('id_user');
            $simpan = $user_model->ubah($data, $id_user);
            if ($simpan) {
                $this->output['sukses'] = true;
                $this->output['pesan']  = 'Data diupdate';
            }

            echo json_encode($this->output);
        }
    }

    public function hapus()
    {
        $user_model = $this->user_model;
        if ($this->request->isAJAX()) {
            $id_user = $this->request->getVar('id_user');
            $hapus = $user_model->hapus($id_user);
            if ($hapus) {
                $this->output['sukses'] = true;
                $this->output['pesan']  = 'Data telah dihapus';
            }

            echo json_encode($this->output);
        }
    }

    public function dt_users()
    {
        $user_model = $this->user_model;
        $where = ['id_user !=' => 0];
        $column_order   = array('', 'nama_user', 'alamat');
        $column_search  = array('nama_user', 'alamat');
        $order = array('nama_user' => 'ASC');
        $list = $user_model->get_datatables('users', $column_order, $column_search, $order, $where);
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $lists) {
            $no++;

            $tomboledit = "<button type=\"button\" class=\"btn btn-info btn-sm\" onclick=\"edit('" . $lists->id_user . "')\"><i class=\"fa fa-tags\"></i></button>";

            $tombolhapus = "<button type=\"button\" class=\"btn btn-danger btn-sm\" onclick=\"hapus('" . $lists->id_user . "')\">
            <i class=\"fa fa-trash\"></i></button>";
        
            $row    = array();
            $row[]  = $no;
            $row[]  = $lists->nama_user;
            $row[]  = $lists->alamat;
            // $row[]  = " . '"' . $lists->id_user . '"' . ")'>EDIT  . '"' . $lists->id_user . '"' . ")'>HAPUS";
            $row[]  = $tomboledit . " " . $tombolhapus;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $user_model->count_all('users', $where),
            "recordsFiltered" => $user_model->count_filtered('users', $column_order, $column_search, $order, $where),
            "data" => $data,
        );

        echo json_encode($output);
    }

}
