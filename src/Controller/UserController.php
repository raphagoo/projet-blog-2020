<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\ChangePassword;
use App\Form\UserCreateType;
use App\Form\UserUpdateType;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function register(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserCreateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userWithEmail = $userRepository->findOneByEmail($user->getEmail());

            // Verification e-mail non existant
            if (!$userWithEmail) {

                $user->setRoles(['ROLE_USER']);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', 'An account already exists with this email');
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/informations", name="profile_private_informations")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function edit_profile(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $userBeforeChange = $this->getUser();
        $form = $this->createForm(UserUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userWithEmail = $userRepository->findOneByEmail($user->getEmail());

            // Verification e-mail non existant
            if (!$userWithEmail || $userWithEmail == $userBeforeChange) {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'Your profile has been successfully updated');
            } else {
                $this->addFlash('danger', 'An account already exists with this email');
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    // See https://openclassrooms.com/forum/sujet/modifier-mon-mot-de-passe
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/editPassword", name="profile_edit_password")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function edit_password(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();
        $changePassword = new ChangePassword();

        $form = $this->createForm('App\Form\UserPasswordType', $changePassword);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('Password')['first']->getData();

            $newEncodedPassword = $passwordEncoder->encodePassword($user, $newPassword);
            $user->setPassword($newEncodedPassword);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Your password has been successfully changed');

            return $this->redirectToRoute('profile_private_informations');
        }

        return $this->render('user/edit_password.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/likedArticles", name="profile_liked_articles")
     * @param Request $request
     * @param ArticleRepository $articleRepository
     * @return Response
     */
    public function profileLikedArticles(Request $request, ArticleRepository $articleRepository): Response
    {
        $user = $this->getUser();
        $articles = $articleRepository->findLikedArticles($user);

        return $this->render('user/liked_articles.html.twig', [
            'user' => $user,
            'articles' => $articles
        ]);
    }
}
