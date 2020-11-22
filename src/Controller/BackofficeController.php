<?php

namespace App\Controller;

use App\BL\ArticleManager;
use App\BL\CommentManager;
use App\BL\UserManager;
use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleFormType;
use App\Form\UserCreateFromAdminType;
use App\Form\UserCreateType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackofficeController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ArticleManager
     */
    private $articleManager;
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var CommentManager
     */
    private $commentManager;

    public function __construct(EntityManagerInterface $em)
    {

        $this->articleManager = new ArticleManager($em);
        $this->userManager = new UserManager($em);
        $this->commentManager = new CommentManager($em);
        $this->em = $em;
    }

    /**
     * @Route("/backoffice", name="backoffice")
     */
    public function index(): Response
    {
        return $this->render('backoffice/index.html.twig', [
            'controller_name' => 'BackofficeController',
        ]);
    }

    /**
     * @Route ("/backoffice/article", name="backofficeArticle")
     */
    public function articles(): Response
    {
        $articles = $this->articleManager->getArticles();
        return $this->render('backoffice/articles.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route ("/backoffice/addArticle", name="backofficeAddArticle")
     * @return Response
     */
    public function addArticle(): Response
    {
       return $this->redirectToRoute('addArticle');
    }

    /**
     * @Route("/backoffice/editArticle/{idArticle}", name="backofficeEditArticle")
     * @param $idArticle
     * @return RedirectResponse|Response
     */
    public function editArticle($idArticle)
    {
        return $this->redirectToRoute('editArticle', ['idArticle' => $idArticle]);
    }

    /**
     * @Route ("/backoffice/comments", name="backofficeComment")
     * @return Response
     */
    public function commentList()
    {
        $comments = $this->commentManager->getComments();
        return $this->render('backoffice/comments.html.twig', ['comments' => $comments]);
    }

    /**
     * @Route ("/backoffice/comment/{idComment}", name="editComment")
     * @param $idComment
     * @return Response
     */
    public function editComment($idComment){
        $comment = $this->commentManager->getCommentById($idComment);
        return $this->render('backoffice/editComment.html.twig', ['comment' => $comment]);
    }

    /**
     * @Route ("/backoffice/comment/{idComment}/{approved}", name="approveComment")
     * @param $idComment
     * @param $approved
     * @return RedirectResponse
     */
    public function approveComment($idComment, $approved){
        $comment = $this->commentManager->getCommentById($idComment);
        $comment->setApproved($approved);
        $this->commentManager->saveData($comment);
        return $this->redirectToRoute('backofficeComment');
    }


    /**
     * @Route ("/backoffice/users/addUser", name="addUser")
     * @param Request $request
     * @return RedirectResponse
     */
    public function register(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        dump("MY USER ! ");
        dump($user);
        $form = $this->createForm(UserCreateFromAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userWithEmail = $userRepository->findOneByEmail($user->getEmail());

            // Verification e-mail non existant
            if (!$userWithEmail) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'The new user has been successfully created');
                dump("I'M BEEING REDIRECTED HERE");
            } else {
                $this->addFlash('danger', 'An account already exists with this email');
            }
        }

        return $this->render('user/addFromBackoffice.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
