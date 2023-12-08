<?php

namespace App\Controller;

use App\Services\PagesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebPagesIndexController extends AbstractController
{
    private $pages_services;
    private $request;

    public function __construct(PagesService $pages_services)
    {
        $this->pages_services = $pages_services;
        $this->request = new Request();
    }
    
    // Index Page
    // -------------------------------------------------------------------------------------------
    #[Route('/{_locale}', name: 'web_index', requirements: ['_locale' => 'fr|en'])]
    public function index(Request $request): Response
    {
        return $this->redirectBase();
    }

    // Other Page
    // -------------------------------------------------------------------------------------------
    #[Route('/{_locale}/{page_slug}', name: 'web_page', requirements: ['_locale' => 'fr|en'])]
    public function page(Request $request, string $page_slug): Response
    {
        return $this->redirectBase();
    }
    
    // Post Page
    // -------------------------------------------------------------------------------------------
    #[Route('/{_locale}/blog/{post_slug}', name: 'web_post', requirements: ['_locale' => 'fr|en'])]
    public function post(string $post_slug): Response
    {
        return $this->redirectBase();
    }

    // Redirections
    // -------------------------------------------------------------------------------------------
    #[Route('/', name: 'web_redirect')]
    public function redirectBase(){
        return $this->redirectToRoute('app_admin');
    }

    // API
    // -------------------------------------------------------------------------------------------
}
