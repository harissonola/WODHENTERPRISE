<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $date = new DateTimeImmutable();


        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'date' => $date,
        ]);
    }


    #[Route('/profile/settings', name: 'app_profile_settings')]
    public function settings(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $user = $userRepository->find($this->getUser());

        $date = new DateTimeImmutable();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            $image = $form->get('image')->getData();
            if ($image) {
                $newFilename = uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('users_img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_register');
                }

                $user->setImage($newFilename);

                $em->persist($user);
                $em->flush();
            }



            $em->persist($user);
            $em->flush();
        }



        return $this->render('profile/settings.html.twig', [
            'controller_name' => 'ProfileController',
            'date' => $date,
            'form' => $form->createView(),
        ]);
    }
}
