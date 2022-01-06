<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Form\AuteurType;
use App\Repository\AuteurRepository;
use DateTimeInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Faker\Factory;

#[Route('/auteur')]
class AuteurController extends AbstractController
{
    private $faker;
    private $donnees;
    #[Route('/', name: 'auteur_index', methods: ['POST', 'GET'])]
    public function index(AuteurRepository $auteurRepository, Request $request, PaginatorInterface $paginator): Response
    {
        if ($request->isMethod("POST")) {
            if ($request->get('recherche')) {
                $auteur = $auteurRepository->findBy(array('nom_prenom' => $request->get('recherche')));
                if ($auteur != null) {
                    $this->donnees  = $auteur;
                }
            }
        } else {
            $this->donnees = $auteurRepository->findBy([], ['id' => 'asc']);
        }

        return $this->render('auteur/index.html.twig', [
            'auteurs' => $this->donnees,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'auteur_new', methods: ['GET', 'POST'])]

    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $auteur = new Auteur();
        $form = $this->createForm(AuteurType::class, $auteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($auteur);
            $entityManager->flush();
            $this->addFlash('success', 'Nouveau auteur a été ajouté');
            return $this->redirectToRoute('auteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('auteur/new.html.twig', [
            'auteur' => $auteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'auteur_show', methods: ['GET'])]
    public function show(Auteur $auteur): Response
    {
        return $this->render('auteur/show.html.twig', [
            'auteur' => $auteur,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'auteur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Auteur $auteur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AuteurType::class, $auteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('auteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('auteur/edit.html.twig', [
            'auteur' => $auteur,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'auteur_delete', methods: ['POST'])]
    public function delete(Request $request, Auteur $auteur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $auteur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($auteur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('auteur_index', [], Response::HTTP_SEE_OTHER);
    }
    public function __construct(EntityManagerInterface $entityManager, AuteurRepository $auteurRepository)
    {
        $this->faker = Factory::create();
        $auteurs = $auteurRepository->findAll();
        if ($auteurs == null) {
            for ($i = 1; $i <= 20; $i++) {
                $auteur = new Auteur();
                $auteur->setNomPrenom($this->faker->name);
                $auteur->setDateDeNaissance(new \DateTime);
                if ($i % 2 == 0) {
                    $auteur->setSexe('female');
                } else {
                    $auteur->setSexe('male');
                }
                $auteur->setNationalite($this->faker->city);

                $entityManager->persist($auteur);
            }
            $entityManager->flush();
        }
    }
}
