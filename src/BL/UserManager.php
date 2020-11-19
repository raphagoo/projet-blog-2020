<?php


namespace App\BL;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;


    /**
     * @return User[]
     */
    public function getUsers(){
        return $this->em->getRepository(User::class)->findAll();
    }

    /**
     * @param $idUser
     * @return User|null
     */
    public function findUserById($idUser){
        return $this->em->getRepository(User::class)->find($idUser);
    }
}
