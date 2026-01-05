<?php
namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Carrega o helper de URL para usar base_url()
        helper('url');
        
        $data = [
            'titulo' => 'Sistema de Tributação Municipal - STM',
            'subtitulo' => 'Prefeitura Municipal de Senador Cortes - MG',
            'sistema' => 'Gestão de Contribuintes'
        ];
        
        return view('home', $data);
    }
}