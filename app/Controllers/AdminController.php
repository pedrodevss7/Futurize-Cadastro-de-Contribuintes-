<?php

namespace App\Controllers;

class AdminController extends BaseController
{
    public function dashboard()
    {
        $data = [
            'title' => 'Painel Administrativo',
            'user' => session()
        ];
        
        return view('admin/dashboard', $data);
    }
}