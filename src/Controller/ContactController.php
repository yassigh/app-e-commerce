<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'admin_contact')]
    public function contact(): Response
    {
        // Logique de votre page de contact
        return $this->render('admin/contact.html.twig');
    }
}


