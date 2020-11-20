<?php

namespace App\Controller;

use App\BL\ArticleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactFormType;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{

    private $em;
    /**
     * @var ArticleManager
     */
    private $articleManager;


    public function __construct(EntityManagerInterface $em)
    {

        $this->articleManager = new ArticleManager($em);
    }

    /**
     * @Route("/", name="default")
     */
    public function index(): Response
    {
        $articles = $this->articleManager->getArticles();
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController', 'articles' => $articles
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(): Response
    {
        return $this->render('default/about.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->addFlash(
                'info',
                'Message sumbit'
            );
        }

        return $this->render('default/contact.html.twig', [
            'controller_name' => 'DefaultController',
            'form' => $form->createView(),
        ]);
    }

    
}
