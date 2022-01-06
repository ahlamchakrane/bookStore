<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Form\GenreType;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Faker\Factory;

#[Route('/genre')]
class GenreController extends AbstractController
{
    private $faker;
    private $genres;

    #[Route('/', name: 'genre_index', methods: ['GET', 'POST'])]
    public function index(GenreRepository $genreRepository, Request $request): Response
    {
        if ($request->isMethod("POST")) {
            if ($request->get('recherche')) {
                $genre = $genreRepository->findBy(array('nom' => $request->get('recherche')));
                if ($genre != null) {
                    $this->genres  = $genre;
                }
            }
        } else {
            $this->genres = $genreRepository->findBy([], ['id' => 'asc']);
        }
        return $this->render('genre/index.html.twig', [
            'genres' => $this->genres,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'genre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($genre);
            $entityManager->flush();
            $this->addFlash('success', 'Nouveau genre a été ajouté');

            return $this->redirectToRoute('genre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('genre/new.html.twig', [
            'genre' => $genre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'genre_show', methods: ['GET'])]
    public function show(Genre $genre): Response
    {
        return $this->render('genre/show.html.twig', [
            'genre' => $genre,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]

    #[Route('/{id}/edit', name: 'genre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Genre $genre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('genre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('genre/edit.html.twig', [
            'genre' => $genre,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]

    #[Route('/{id}', name: 'genre_delete', methods: ['POST'])]
    public function delete(Request $request, Genre $genre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $genre->getId(), $request->request->get('_token'))) {

            if ($genre->getLivres()->isEmpty()) {
                $entityManager->remove($genre);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('genre_index', [], Response::HTTP_SEE_OTHER);
    }
    public function __construct(EntityManagerInterface $entityManager, GenreRepository $genreRepository)
    {
        $this->faker = Factory::create();
        $genres = $genreRepository->findAll();
        if ($genres == null) {
            for ($i = 1; $i <= 10; $i++) {
                $genre = new genre();
                $genre->setNom($this->faker->name);
                $entityManager->persist($genre);
            }
            $entityManager->flush();
        }
    }
}
