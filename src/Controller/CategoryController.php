<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\BL\CategoryManager;
use App\Form\CategoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

;

class CategoryController extends AbstractController
{

    public function __construct(EntityManagerInterface $em)
    {

        $this->categoryManager = new CategoryManager($em);
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
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->categoryManager->GetInscriptionData($category);
            return $this->redirectToRoute('category');
        }
        return $this->render('category/categoryAdd.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("category/edit/{idCategory}", name="editCategory")
     * @param $idCategory
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function getModifyCategory($idCategory, Request $request)
    {
        $category = $this->categoryManager->getCategoryById($idCategory);
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->categoryManager->GetInscriptionData($category);
            return $this->redirectToRoute('category');
        }
        return $this->render('category/categoryEdit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("category/delete/{idCategory}",name="deleteCategory")
     * @param $idCategory
     * @return RedirectResponse|Response
     */
    public function deleteCategory($idCategory)
    {
        $category = $this->categoryManager->getCategoryById($idCategory);
        $this->categoryManager->deleteCategory($category);
        return $this->redirectToRoute('category');
    }
}
