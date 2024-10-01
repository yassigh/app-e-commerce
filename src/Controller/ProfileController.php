<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Orders;
use App\Form\ProfilePictureType;
use App\Form\ProfileUpdateType;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'user')]
    public function index(UsersRepository $userRepository, OrdersRepository $ordersRepository): Response
    {
        $user = $this->getUser();
    
        // Get all generations for the user
        $allGenerations = $userRepository->getAllGenerations($user);
    
        // Calculate the number of team members
        $teamMembersCount = array_sum(array_map('count', $allGenerations));
        $generationCounts = array_map('count', $allGenerations);
    
        // Fetch orders for the current user
        $orders = $user->getOrders();
    
        $totalConsumption = $this->calculateTotalConsumption($orders);
    
        $teamConsumption = $this->calculateTeamConsumption($allGenerations, $ordersRepository);
    
        $monthlyConsumption = $this->calculateMonthlyConsumption($ordersRepository, $user);
    
        // Calculate order commission using a private method
        $orderCommission = $this->calculateOrderCommission($orders);
        
        // Set parent comes
        $this->setParentComes($ordersRepository, $userRepository, $user);
    
        return $this->render('profile/index.html.twig', [
            'teamMembersCount' => $teamMembersCount,
            'generationCounts' => $generationCounts,
            'totalConsumption' => $totalConsumption,
            'teamConsumption' => $teamConsumption,
            'monthlyConsumption' => $monthlyConsumption,
            'orders' => $orders,
            'orderCommission' => $orderCommission,
        ]);
    }

    

    
    
    private function calculateOrderCommission($orders): float
    {
        $orderCommission = 0;

        foreach ($orders as $order) {
            // Assuming there is a getTotalAmount() method in your Order entity
            
            $orderCommission += 0.2 * $order->getTotalAmount();
        }

        return $orderCommission;
    }
    private function calculateTeamConsumption($allGenerations, $ordersRepository): int
    {
        $teamConsumption = 0;
        foreach ($allGenerations as $generation) {
            foreach ($generation as $teamMember) {
                $orders = $ordersRepository->findBy(['users' => $teamMember]);

                foreach ($orders as $order) {
                    foreach ($order->getOrdersDetails() as $orderDetail) {
                        $teamConsumption += $orderDetail->getTotal();
                    }
                }
            }
        }

        return $teamConsumption;
    }

    private function setParentComes($ordersRepository,UsersRepository $usersRepository, $user)
    {
        $comission= 0;
        $user = $this->getUser();
       





        
    }


    private function calculateMonthlyConsumption($ordersRepository, $user): int
    {
        $user = $this->getUser();
        

        // Calculate the date 30 days ago from today
        $startDate = new \DateTimeImmutable('-30 days');

        // Fetch orders for the last 30 days for the current user
        $monthlyOrders = $ordersRepository->findMonthlyOrders($user, $startDate);

        $monthlyConsumption = $this->calculateTotalConsumption($monthlyOrders);

        return $monthlyConsumption;
    }
    private function calculateTotalConsumption($orders): int
    {
        $totalConsumption = 0;

        foreach ($orders as $order) {
            foreach ($order->getOrdersDetails() as $orderDetail) {
                $totalConsumption +=$orderDetail->getTotal();
            }
        }

        return $totalConsumption;
    }



    #[Route('/update-picture', name: 'update_picture')]
    public function updateProfilePicture(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfilePictureType::class, $user);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form['photo']->getData();
            if ($photo) {
                $photoDirectory = $this->getParameter('profile_photo_directory'); // Répertoire où stocker les photos de profil
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $photo->move($photoDirectory, $fileName);
               
    
                $entityManager->persist($user);
                $entityManager->flush();
    
                $this->addFlash('success', 'Profile picture updated successfully.');
            } else {
                $this->addFlash('danger', 'Profile picture is not set.');
            }
        }
    
        return $this->redirectToRoute('profile_user');
    }
    #[Route('/update-profile', name: 'update_profile')]
    public function updateProfile(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfileUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
           

            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('profile/update_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/link/{id}', name: 'link')]
    public function link(int $id, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the user from the database based on the provided ID
        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // You can access the user's referral code using $user->getReferralCode()
        $referralCode = $user->getReferralCode();

        $registrationLink = $this->generateUrl('app_register', ['referralCode' => $referralCode], true);

        return $this->render('profile/link.html.twig', [
            'user' => $user,
            'referralCode' => $referralCode,
            'registrationLink' => $registrationLink,
        ]);
    }
   
  


}
