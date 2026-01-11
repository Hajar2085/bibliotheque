<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Form\EmpruntType;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/emprunt')]
final class EmpruntController extends AbstractController
{
    #[Route(name: 'app_emprunt_index', methods: ['GET'])]
    public function index(EmpruntRepository $empruntRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
             $emprunts = $empruntRepository->findAll();
        } else {
             $emprunts = $empruntRepository->findBy(['utilisateur' => $this->getUser()]);
        }

        return $this->render('emprunt/index.html.twig', [
            'emprunts' => $emprunts,
        ]);
    }

    #[Route('/new', name: 'app_emprunt_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $emprunt = new Emprunt();
        
        
        // If query param 'livre_id' exists, pre-fill
        if ($livreId = $request->query->get('livre_id')) {
            $livre = $entityManager->getRepository(Livre::class)->find($livreId);
            if ($livre) {
                // Logic check: stock available?
                if ($livre->getNombreExemplaires() <= 0) {
                    $this->addFlash('danger', 'Ce livre n\'est plus disponible.');
                    return $this->redirectToRoute('app_livre_index');
                }
                $emprunt->setLivre($livre);
            }
        }
        
        // Default values
        $emprunt->setDateEmprunt(new \DateTime());
        $emprunt->setDateRetourPrevue((new \DateTime())->modify('+14 days'));
        $emprunt->setStatut('en cours');
        $emprunt->setUtilisateur($this->getUser());


        $form = $this->createForm(EmpruntType::class, $emprunt);
        
        // Assuming we might want to hide user field for regular users but generated crud has it.
        // For now standard crud structure.
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $livre = $emprunt->getLivre();
            if ($livre->getNombreExemplaires() > 0) {
                 $livre->setNombreExemplaires($livre->getNombreExemplaires() - 1);
                 $entityManager->persist($emprunt);
                 $entityManager->flush();
                 $this->addFlash('success', 'Emprunt enregistré avec succès.');
            } else {
                 $this->addFlash('danger', 'Désolé, ce livre n\'est plus en stock au moment de la validation.');
            }

            return $this->redirectToRoute('app_emprunt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emprunt/new.html.twig', [
            'emprunt' => $emprunt,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_emprunt_show', methods: ['GET'])]
    public function show(Emprunt $emprunt): Response
    {
        // Security check
        if ($emprunt->getUtilisateur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException();
        }

        return $this->render('emprunt/show.html.twig', [
            'emprunt' => $emprunt,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_emprunt_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Emprunt $emprunt, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmpruntType::class, $emprunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_emprunt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emprunt/edit.html.twig', [
            'emprunt' => $emprunt,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}/return', name: 'app_emprunt_return', methods: ['POST'])]
    public function returnBook(Emprunt $emprunt, EntityManagerInterface $entityManager): Response 
    {
        // Allow user to return their own book? Or only admin? 
        // User returning usually means they physically bring it back, so admin action.
        // But for this digital system, maybe user can "return" or just admin marks it.
        // Let's assume Admin marks returned.
        if (!$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException();
        }
        
        if ($emprunt->getStatut() !== 'retourné') {
             $emprunt->setStatut('retourné');
             $emprunt->setDateRetourEffective(new \DateTime());
             
             $livre = $emprunt->getLivre();
             $livre->setNombreExemplaires($livre->getNombreExemplaires() + 1);
             
             $entityManager->flush();
             $this->addFlash('success', 'Livre retourné.');
        }

        return $this->redirectToRoute('app_emprunt_index');
    }

    #[Route('/{id}', name: 'app_emprunt_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Emprunt $emprunt, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$emprunt->getId(), $request->request->get('_token'))) {
            // If deleting an active loan, re-increment stock
            if ($emprunt->getStatut() === 'en cours') {
                 $livre = $emprunt->getLivre();
                 $livre->setNombreExemplaires($livre->getNombreExemplaires() + 1);
            }

            $entityManager->remove($emprunt);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_emprunt_index', [], Response::HTTP_SEE_OTHER);
    }
}
