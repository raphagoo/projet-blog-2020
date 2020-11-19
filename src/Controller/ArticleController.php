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

class ArticleController extends AbstractController
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
     * @Route("/article", name="indexArticle")
     */
    public function index(){
        return $this->render('article/index.html.twig', ['controller_name' => 'ArticleController']);
    }

    /**
     * @Route("/article/add", name="addArticle")
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
            return $this->redirectToRoute('index');
        }
        return $this->render('article/addArticle.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/article/edit/{idArticle}", name="editArticle")
     * @param $idArticle
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editArticle($idArticle, Request $request)
    {
        $article = $this->articleManager->findArticleById($idArticle);
        $oldFilename = $article->getImage();
        if($article->getImage() !== null) {
            $article->setImage(
                new File($this->getParameter('images_directory') . '/' . $article->getImage())
            );
        }
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
            else{
                if($article->getImage() !== null) {
                    $article->setImage($oldFilename);
                }
            }
            $article->setAuthor($this->userManager->findUserById(1));
            $this->articleManager->GetInscriptionData($article);
            return $this->redirectToRoute('index');
        }
        return $this->render('article/editArticle.html.twig', [
            'form' => $form->createView(), 'image' => $oldFilename
        ]);
    }

    /**
     * @Route("/article/delete/{idArticle}", name="deleteArticle")
     * @param $idArticle
     */
    public function deleteArticle($idArticle)
    {
        $article = $this->articleManager->findArticleById($idArticle);
        $this->articleManager->deleteArticle($article);
    }

}
