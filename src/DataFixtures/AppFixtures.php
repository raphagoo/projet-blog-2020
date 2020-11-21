<?php


namespace App\DataFixtures;


use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $userExists = $manager->getRepository(User::class)->findOneBy(['email' => 'admin.admin@fixture.fr']);
        if(!$userExists) {
            // create user
            $user = new User();
            $user->setEmail('admin.admin@fixture.fr');
            $user->setFirstName('Admin');
            $user->setLastName('Fixtures');
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword('password');
            $password = $this->encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $manager->persist($user);
            $manager->flush();
        }

        $categoryExists = $manager->getRepository(Category::class)->findOneBy(['name' => 'Politic']);
        if(!$categoryExists) {
            //create category
            $category = new Category();
            $category->setName('Politic');
            $manager->persist($category);
            $manager->flush();
        }

        // create 20 articles! Bam!
        $nbExisting = count($manager->getRepository(Article::class)->findAll());

        for ($i = $nbExisting; $i < $nbExisting + 20; $i++) {
            $article = new Article();
            $article->setTitle('Article '.$i);
            $article->setSubtitle('Subtitle' .$i);
            if($userExists){
                $article->setAuthor($userExists);
            }
            else {
                $article->setAuthor($user);
            }
            if($categoryExists){
                $article->setCategory($categoryExists);
            }
            else {
                $article->setCategory($category);
            }
            $article->setPublicationDate(new \DateTime('now'));
            $article->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ornare dui ultricies orci maximus ultricies. Vestibulum sit amet maximus neque. Nam magna eros, pretium vitae sapien et, dignissim malesuada lorem. Aenean est purus, varius vitae aliquet eget, pretium et odio. Sed bibendum maximus molestie. In ac enim vel sapien ultricies eleifend. In rutrum luctus mauris tincidunt consequat. Nullam elementum lobortis est. Curabitur pharetra nibh augue, rutrum fringilla tortor consequat at. Aenean dictum libero nec sapien imperdiet, at bibendum mi dictum. Nunc ornare ac enim euismod efficitur. Sed et arcu massa. Mauris aliquet nec tellus eu pulvinar.');
            $manager->persist($article);
        }

        $manager->flush();
    }

}
