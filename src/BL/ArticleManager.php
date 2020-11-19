<?php


namespace App\BL;


use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

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

    /**
     * @param Article $article
     */
    public function GetInscriptionData(Article $article){

        $this->em->persist($article);
        $this->em->flush();
    }

    /**
     * @param $idArticle
     * @return Article|null
     */
    public function findArticleById($idArticle){
        return $this->em->getRepository(Article::class)->find($idArticle);
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
