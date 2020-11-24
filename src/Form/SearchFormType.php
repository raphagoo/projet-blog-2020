<?php


namespace App\Form;


use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchFormType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class, ['attr' => ['placeholder' => 'Search for a title'], 'label' => false, 'required' => false])
            ->add('category',EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'name',
                'choice_value' => function (?Category $category) {
                    return $category ? $category->getId() : '';
                },
                'multiple'=> true,
                'expanded'=> true,
                'label' => 'Catégorie : ',
                'mapped' => true,
                'data' => $this->em->getRepository(Category::class)->findAll()
            ])
            ->add('Search', SubmitType::class )
            ->setMethod('GET')
        ;
    }
}
