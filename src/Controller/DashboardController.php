<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route(name: 'app_dashboard')]
    public function index(): Response
    {
        // Simple dashboard redirecting to books for now, or could render a specific dashboard template
        // as requested, let's make it a distinct page or just forward.
        // User asked for "redirigé vers /dashboard", so let's make a real page.
        
        return $this->render('dashboard/index.html.twig');
    }
}
