<?php

namespace App\Controller;

use App\BL\ArticleManager;
use App\BL\UserManager;
use App\Entity\Article;
use App\Form\ArticleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @param Request $request
     * @return Response
     */
    public function addArticle(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $article->setImage($newFilename);
            }
            $article->setAuthor($this->userManager->findUserById(1));
            $this->articleManager->GetInscriptionData($article);
            return $this->redirectToRoute('backofficeArticle');
        }
        return $this->render('backoffice/addArticle.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
