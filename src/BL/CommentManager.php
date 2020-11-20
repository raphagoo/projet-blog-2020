<?php


namespace App\BL;


use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

class CommentManager
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;

    /**
     * @param Comment $comment
     */
    public function saveData(Comment $comment){

        $this->em->persist($comment);
        $this->em->flush();
    }

    public function getComments(){
        return $this->em->getRepository(Comment::class)->findAll();
    }

    /**
     * @param $idComment
     * @return Comment|null
     */
    public function getCommentById($idComment){
        return $this->em->getRepository(Comment::class)->find($idComment);
    }
}
