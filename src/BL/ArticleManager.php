<?php


namespace App\BL;


use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ArticleManager
 * @package App\BL
 */
class ArticleManager
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;


    /**
     * @return Article[]
     */
    public function getArticles(){
        return $this->em->getRepository(Article::class)->findAll();
    }

    public function listArticles(Request $request, $searchTerm = null)
    {
        return $this->em->getRepository(Article::class)->listArticles($request, $searchTerm);
    }

    /**
     * @param Article $article
     * @return int|null
     */
    public function GetInscriptionData(Article $article){

        $this->em->persist($article);
        $this->em->flush();
        return $article->getId();
    }

    /**
     * @param $idArticle
     * @return Article|null
     */
    public function findArticleById($idArticle){
        return $this->em->getRepository(Article::class)->find($idArticle);
    }

    public function getRecentArticles($idArticle)
    {
        return $this->em->getRepository(Article::class)->findRecents($idArticle);
    }

    /**
     * @param $article
     */
    public function deleteArticle($article)
    {
        $this->em->remove($article);
        $this->em->flush();
    }
}
