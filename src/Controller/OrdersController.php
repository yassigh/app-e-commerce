<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/orders', name: 'orders_')]
class OrdersController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(SessionInterface $session, Request $request, ProductsRepository $productsRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
    
        $panier = $session->get('panier', []);
        if (empty($panier)) {
            $referer = $request->headers->get('referer') ?: $this->generateUrl('fallback_route');
            return $this->redirect($referer);
        }
    
        $order = new Orders();
        $order->setUsers($this->getUser());
        $order->setReference('ORDER_' . date('YmdHis') . '_' . uniqid());
    
        foreach ($panier as $item => $quantity) {
            $product = $productsRepository->find($item);
    
            if (!$product) {
                // Gérer les produits qui n'existent plus
                continue;
            }
    
            // Vérifiez si le stock est suffisant
            if ($product->getStock() < $quantity) {
                $this->addFlash('error', "Le produit {$product->getName()} n'a pas assez de stock.");
                return $this->redirectToRoute('cart_index');
            }
    
            // Mettez à jour le stock
            $product->setStock($product->getStock() - $quantity);
    
            // Créez les détails de la commande
            $orderDetails = new OrdersDetails();
            $orderDetails->setProducts($product);
            $orderDetails->setPrice($product->getPrice());
            $orderDetails->setQuantity($quantity);
    
            $order->addOrdersDetail($orderDetails);
        }
    
        $em->persist($order);
        $em->flush();
    
        // Videz le panier
        $session->remove('panier');
    
        return $this->render('orders/index.html.twig', [
            'controller_name' => 'OrdersController',
        ]);
    }
    
}
