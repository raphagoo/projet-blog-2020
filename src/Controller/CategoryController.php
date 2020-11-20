<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\BL\CategoryManager;
use App\Form\CategoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\Request;

;

class CategoryController extends AbstractController
{

    public function __construct(EntityManagerInterface $em)
    {

        $this->optionManager = new CategoryManager($em);
        $this->em = $em;
    }

    /**
     * @var CategoryManager
     */
    private $categoryManager;
    /**
     * @Route("/category", name="category")
     */
    public function index(): Response
    {
        $listCategory =  $this->categoryManager->getCategoryList();
        return $this->render('category/index.html.twig', ['listCategory' => $listCategory]);
        
    }

    /**
     * @Route("category/add", name="addCategory")
     * @param Request $request
     * @return Response
     */
    public function addCategory(Request $request)
    {

        $category = new Category();
        $form = $this->createForm(OptionFormType::class, $option);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->optionManager->GetInscriptionData($option);
            return $this->redirectToRoute('GetListOption');
        }
        return $this->render('backoffice/option/optionAdd.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/option/edit/{idOption}", name="editOption")
     * @param $idOption
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function getModifyAgent($idOption, Request $request)
    {
        $option = $this->optionManager->getOptionById($idOption);
        $form = $this->createForm(OptionFormType::class, $option);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->optionManager->GetInscriptionData($option);
            return $this->redirectToRoute('GetListOption');
        }
        return $this->render('backoffice/option/optionEdit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/option/delete/{idOption}",name="deleteOption")
     * @param $idOption
     * @return RedirectResponse|Response
     */
    public function deleteRoom($idOption)
    {
        $option = $this->optionManager->getOptionById($idOption);
        $this->optionManager->deleteOption($option);
        return $this->redirectToRoute('GetListOption');
    }
}
