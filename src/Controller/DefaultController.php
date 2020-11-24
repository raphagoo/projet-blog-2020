<?php

namespace App\Controller;

use App\BL\ArticleManager;
use App\BL\UserManager;
use App\Form\NewsletterFormType;
use App\Form\SearchFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /**
     * @var UserManager
     */
    private $userManager;


    public function __construct(EntityManagerInterface $em)
    {
        $this->articleManager = new ArticleManager($em);
        $this->userManager = new UserManager($em);
    }

    /**
     * @Route("/", name="default")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $articles = $this->articleManager->listArticles($request);
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchTerm = $form->get('search')->getData();
            $articles = $this->articleManager->listArticles($request, $searchTerm);
        }
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController', 'articles' => $articles, 'form' => $form->createView()
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
     * @param Request $request
     * @return Response
     */
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->addFlash(
                'info',
                'Message submit'
            );
        }

        return $this->render('default/contact.html.twig', [
            'controller_name' => 'DefaultController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter", name="newsletter")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newsletter(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(NewsletterFormType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->userManager->GetInscriptionData($user);
            return $this->redirectToRoute('default');
        }
        return $this->render('default/newsletter.html.twig', ['form' => $form->createView()]);
    }

    
}
