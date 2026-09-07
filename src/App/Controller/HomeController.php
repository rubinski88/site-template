<?php

declare(strict_types=1);

namespace App\Controller;

class HomeController
{
    public function index()
    {
        require ROOT_PATH . 'templates/home/index.php';
    }
}
