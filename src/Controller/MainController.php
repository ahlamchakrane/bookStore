<?php

namespace App\Controller;

use App\Repository\AuteurRepository;
use App\Repository\GenreRepository;
use App\Repository\LivreRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MainController extends AbstractController
{
    #[Route('/', name: 'acceuil')]
    public function index(AuthenticationUtils $authenticationUtils, GenreRepository $genreRepository, AuteurRepository $auteurRepository, LivreRepository $livreRepository, UserRepository $userRepository): Response
    {


        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        $auteurs = $auteurRepository->findBy([], ['id' => 'asc']);
        $genres = $genreRepository->findAll();
        $livres = $livreRepository->findAll();
        $users = $userRepository->findAll();
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'genres' => $genres,
            'auteurs' => $auteurs,
            'livres' => $livres,
            'users' => $users,
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }
}
