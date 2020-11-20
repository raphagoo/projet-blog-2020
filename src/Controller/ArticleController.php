<?php

namespace App\Controller;

use App\BL\ArticleManager;
use App\BL\CommentManager;
use App\BL\LikeManager;
use App\BL\UserManager;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Like;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

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
    /**
     * @var CommentManager
     */
    private $commentManager;
    /**
     * @var LikeManager
     */
    private $likeManager;

    public function __construct(EntityManagerInterface $em)
    {

        $this->articleManager = new ArticleManager($em);
        $this->userManager = new UserManager($em);
        $this->commentManager = new CommentManager($em);
        $this->likeManager = new LikeManager($em);
        $this->em = $em;
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
            $articleId = $this->articleManager->GetInscriptionData($article);
            return $this->redirectToRoute('viewArticle', ['idArticle' => $articleId]);
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
            $articleId = $this->articleManager->GetInscriptionData($article);
            return $this->redirectToRoute('viewArticle', ['idArticle' => $articleId]);
        }
        return $this->render('article/editArticle.html.twig', [
            'form' => $form->createView(), 'image' => $oldFilename, 'article' => $article
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

    /**
     * @Route("/article/{idArticle}", name="viewArticle")
     * @param $idArticle
     * @param Request $request
     * @param Security $security
     * @return Response
     */
    public function viewArticle($idArticle, Request $request, Security $security){
        $article = $this->articleManager->findArticleById($idArticle);

        $date = $article->getPublicationDate();
        $stringDate = $date->format('Y-m-d H:i:s');

        $comments = $article->getComments();
        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setAuthor($security->getUser());
        $liked = false;
        $likes = $article->getLikes();
        $nbLikes = count($likes);
        foreach ($likes as $like){
            if($like->getAuthor() === $this->getUser()){
                $liked = true;
                break;
            }
        }

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $comment->setCreationDate(new \DateTime('now'));
            $this->commentManager->saveData($comment);
            return $this->redirectToRoute('viewArticle', ['idArticle' => $idArticle]);
        }

        return $this->render('article/index.html.twig', ['article' => $article, 'stringDate' => $stringDate, 'form' => $form->createView(), 'comment' => $comment, 'commentList' => $comments, 'liked' => $liked, 'nbLikes' => $nbLikes]);
    }

    /**
     * @Route ("/article/{idArticle}/{liked}", name="likeArticle")
     * @param $idArticle
     * @param $liked
     * @return RedirectResponse
     */
    public function likeArticle($idArticle, $liked)
    {
        $article = $this->articleManager->findArticleById($idArticle);
        if($liked === 'true'){
            $like = new Like();
            $like->setArticle($article);
            $like->setAuthor($this->getUser());
            $like->setDateLike(new \DateTime('now'));
            $this->likeManager->saveData($like);
        }
        else{
            $likes = $article->getLikes();
            foreach ($likes as $like){
                if($like->getAuthor() === $this->getUser()){
                    $this->likeManager->deleteLike($like);
                }
            }
        }
        return $this->redirectToRoute('viewArticle', ['idArticle' => $idArticle]);
    }

}
