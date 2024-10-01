<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UsersRepository;

#[Route('/admin/users', name: 'admin_users_')]
class UsersController extends AbstractController {
    
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    { $users = $usersRepository->findAll();
        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }


 






}