<?php

namespace App\Controller;

use App\Entity\Shop;

use App\Entity\Products;
use App\Entity\SubCategories;
use App\Form\ProductSearche;
use App\Repository\ShopRepository;

use App\Repository\ProductsRepository;
use App\Repository\SubCategoriesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/products', name: 'products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'all_products')]
    public function indexx(Request $request, ProductsRepository $productsRepository): Response
    {    
        $form = $this->createForm(ProductSearche::class);
        $form->handleRequest($request);
    
        $results = [];
    
        if ($form->isSubmitted() && $form->isValid()) {
            $keyword = $form->get('keyword')->getData();
            $results = $productsRepository->findBy(['name' => $keyword]);
        } else {
            // Fetch all products when the form is not submitted
            $results = $productsRepository->findAll();
        }
    
        return $this->render('products/indexx.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
        ]);
    }


    #[Route('/{id}', name: 'Products')]
    public function child(SubCategories $subcategories ,Shop $shop ,ProductsRepository $productsRepository, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $products = $productsRepository->findProductsPaginated($page,$subcategories->getId(), $shop->getId());
      
       
        return $this->render('products/index.html.twig', [
            'shop'=> $shop,
            'products' => $products,
            'subcategories'=> $subcategories,
            
            
         
        ]);
    
    }
    

    #[Route('/details/{id}', name: 'details')]
public function details(Products $product, ProductsRepository $productsRepository): Response
{
    // Fetch related products based on the subcategory of the main product
    $relatedProducts = $productsRepository->findBy(['subcategories' => $product->getSubcategories()]);

    return $this->render('products/details.html.twig', [
        
        'product' => $product,
        'relatedProducts' => $relatedProducts,
    ]);
}


#[Route('/search', name: 'search_products')]
public function search(Request $request, ProductsRepository $productsRepository): Response
{
    $form = $this->createForm(ProductSearche::class);
    $form->handleRequest($request);

    $results = [];

    if ($form->isSubmitted() && $form->isValid()) {
        $keyword = $form->get('keyword')->getData();
        $results = $productsRepository->searchProducts($keyword);
    } else {
        // Fetch all products when the form is not submitted
        $results = $productsRepository->findAll();
    }


    // Rendre la vue des résultats de recherche avec les produits et le terme de recherche
    return $this->render('products/indexx.html.twig', [
        'form' => $form->createView(),
        'results' => $results,
       
          ]);
}



    }
