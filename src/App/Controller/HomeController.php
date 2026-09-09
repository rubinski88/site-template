<?php

declare(strict_types=1);

namespace App\Controller;

use Framework\Routing\Route;
use Framework\View;

class HomeController
{
    #[Route('/')]
    public function index()
    {
        $view = new View();

        echo $view->render(
            'home/index'
        );
    }
}
