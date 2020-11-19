<?php

namespace App\Controller;

use App\BL\ArticleManager;
use App\BL\UserManager;
use App\Entity\Article;
use App\Form\ArticleFormType;
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

    public function __construct(EntityManagerInterface $em)
    {

        $this->articleManager = new ArticleManager($em);
        $this->userManager = new UserManager($em);
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
     * @Route("/editArticle/{idArticle}", name="backofficeEditArticle")
     * @param $idArticle
     * @return RedirectResponse|Response
     */
    public function editArticle($idArticle)
    {
        return $this->redirectToRoute('editArticle', ['idArticle' => $idArticle]);
    }
}
