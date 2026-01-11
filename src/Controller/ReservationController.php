<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Livre;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
         if ($this->isGranted('ROLE_ADMIN')) {
             $reservations = $reservationRepository->findAll();
        } else {
             $reservations = $reservationRepository->findBy(['utilisateur' => $this->getUser()]);
        }
        
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        
        if ($livreId = $request->query->get('livre_id')) {
            $livre = $entityManager->getRepository(Livre::class)->find($livreId);
            if ($livre) {
                 if ($livre->getNombreExemplaires() > 0) {
                      $this->addFlash('warning', 'Ce livre est disponible, vous pouvez l\'emprunter directement.');
                      return $this->redirectToRoute('app_emprunt_new', ['livre_id' => $livre->getId()]);
                 }
                $reservation->setLivre($livre);
            }
        }
        
        $reservation->setDateReservation(new \DateTime());
        $reservation->setStatut('active');
        $reservation->setUtilisateur($this->getUser());


        $form = $this->createForm(ReservationType::class, $reservation);
        // Assuming ReservationType is standard generated one, we might need to adjust it to not conform user input for date/status/user
        // But for now let's just handle submission.
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
             // Double check availability
             $livre = $reservation->getLivre();
             if ($livre->getNombreExemplaires() > 0) {
                 $this->addFlash('warning', 'Le livre est redevenu disponible entre temps !');
                  return $this->redirectToRoute('app_livre_index');
             }

            $entityManager->persist($reservation);
            $entityManager->flush();
            
            $this->addFlash('success', 'Réservation effectuée.');

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
         if ($reservation->getUtilisateur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException();
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $reservation->getUtilisateur() !== $this->getUser()) {
             throw $this->createAccessDeniedException();
        }
        
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
