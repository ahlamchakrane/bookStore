<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Entity\Genre;
use App\Entity\Livre;
use App\Form\GenreType;
use App\Form\LivreType;
use App\Repository\AuteurRepository;
use App\Repository\GenreRepository;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Faker\Factory;
use Symfony\Component\Validator\Constraints\Length;

#[Route('/livre')]
class LivreController extends AbstractController
{
    private $faker;
    private $livres;
    #[Route('/', name: 'livre_index', methods: ['GET', 'POST'])]
    public function index(LivreRepository $livreRepository, AuteurRepository $auteurRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod("POST")) {
            if ($request->get('titre'))
                $this->livres = $livreRepository->findBy(array('titre' => $request->get('titre')));
            else if ($request->get('auteur')) {
                $this->livres = $auteurRepository->findBy(array('nom_prenom' => $request->get('auteur')));
                return $this->renderForm('auteur/index.html.twig', [
                    'auteurs' => $this->livres,
                ]);
            } else
                $this->livres = $livreRepository->findAll();
        } else {
            $this->livres = $livreRepository->findAll();
        }

        return $this->renderForm('livre/index.html.twig', [
            'livres' => $this->livres,
        ]);
    }
    #[Route('/titreFilter', name: 'livre_titreFilter')]
    public function titreFilter(LivreRepository $livreRepository): Response
    {
        $this->livres = $livreRepository->findBy(array(), array('titre' => 'ASC'));
        return $this->renderForm('livre/index.html.twig', [
            'livres' => $this->livres,
        ]);
    }
    #[Route('/dateFilter', name: 'livre_dateFilter')]
    public function dateFilter(LivreRepository $livreRepository): Response
    {
        $this->livres = $livreRepository->findBy(array(), array('date_de_parution' => 'ASC'));
        return $this->renderForm('livre/index.html.twig', [
            'livres' => $this->livres,
        ]);
    }
    #[Route('/noteFilter', name: 'livre_noteFilter')]
    public function noteFilter(LivreRepository $livreRepository): Response
    {
        $this->livres = $livreRepository->findBy(array(), array('note' => 'ASC'));
        return $this->renderForm('livre/index.html.twig', [
            'livres' => $this->livres,
        ]);
    }


    #[IsGranted('ROLE_ADMIN')]

    #[Route('/new', name: 'livre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livre);
            $entityManager->flush();

            $this->addFlash(
                'info',
                'Nouveau livre a été ajouté'
            );

            return $this->redirectToRoute('livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('livre/new.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'livre_show', methods: ['GET'])]
    public function show(Livre $livre): Response
    {
        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]

    #[Route('/{id}/edit', name: 'livre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('livre/edit.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]

    #[Route('/{id}', name: 'livre_delete', methods: ['POST'])]
    public function delete(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $livre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($livre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('livre_index', [], Response::HTTP_SEE_OTHER);
    }
    public function __construct(EntityManagerInterface $entityManager, LivreRepository $livreRepository, AuteurRepository $auteurRepository, GenreRepository $genreRepository)
    {
        $this->faker = Factory::create();
        $this->livres = $livreRepository->findAll();

        if ($this->livres == null) {
            for ($i = 0; $i <= 50; $i++) {
                $auteurs_rand = random_int(1, 3);
                $auteurs_size = count($auteurRepository->findAll());

                $genres_rand = random_int(1, 3);
                $genres_size = count($genreRepository->findAll());
                $livre = new livre();
                $livre->setIsbn($this->faker->isbn13);
                $livre->setNombrePage($this->faker->numberBetween(50, 100));
                $livre->setNote($this->faker->numberBetween(0, 20));
                $livre->setTitre($this->faker->word());
                $livre->setDateDeParution(new \DateTime(rand(1990, 2020)));
                for ($j = 0; $j < $auteurs_rand; $j++) {
                    $auteur_to_livre_rand = random_int(1, $auteurs_size);
                    $auteur = $auteurRepository->findOneById($auteur_to_livre_rand);
                    $livre->addAuteur($auteur);
                }
                for ($k = 0; $k < $genres_rand; $k++) {
                    $genre_to_livre_rand = random_int(1, $genres_size);
                    $genre = $genreRepository->findOneById($genre_to_livre_rand);
                    $livre->addGenre($genre);
                }
                $entityManager->persist($livre);
            }
            $entityManager->flush();
        }
    }

    public function rechercher(LivreRepository $livreRepository, Request $request)
    {
        $this->livres = $livreRepository->findAll();
        if ($request->isMethod("POST")) {
            $titre = $request->get('titre');
            $this->livres = $livreRepository->findBy(array('titre' => $titre));
        }
        return $this->renderForm('livre/index.html.twig', [
            'livres' => $this->livres,
        ]);
    }
}
