<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'admin_about')]
    public function about(): Response
    {
        // Logique de votre page de contact
        return $this->render('admin/about.html.twig');
    }
}


