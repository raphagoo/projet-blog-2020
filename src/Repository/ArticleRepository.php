<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $knp;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $knp)
    {
        parent::__construct($registry, Article::class);
        $this->knp = $knp;
    }

    public function listArticles(Request $request, $searchTerm = null)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT a
            FROM App\Entity\Article a
            WHERE a.title LIKE :searchTerm'
        )
            ->setParameter('searchTerm', '%'.$searchTerm.'%');

        return $this->knp->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

    }

    /**
     * @param $idArticle
     * @return Article[]
     */
    public function findRecents($idArticle): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a
            FROM App\Entity\Article a
            WHERE a.id != :idArticle
            ORDER BY a.publicationDate DESC'
        )
            ->setParameter('idArticle', $idArticle)
            ->setMaxResults(4);

        // returns an array of Article objects
        return $query->getResult();
    }

    /**
     * @return Article[]
     */
    public function findRecentsBack(): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a
            FROM App\Entity\Article a
            ORDER BY a.publicationDate DESC'
        )
            ->setMaxResults(5);

        // returns an array of Article objects
        return $query->getResult();
    }

    /**
     * @param UserInterface $user
     * @return Article[] Returns an array of Article objects
     */
    public function findLikedArticles(UserInterface $user)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.likes', 'l')
            ->andWhere('l.author = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }
    // $qb = $this->_em->createQueryBuilder();
    // $qb->select('t, c')
    //     ->from('AppBundle:Transactional','t')
    //     ->join('t.fkCustomer', 'c');

    // return $qb->getQuery()->execute();


    //     SELECT article.* FROM article , like
    // WHERE like.Article =:article AND like.user = :user




    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
