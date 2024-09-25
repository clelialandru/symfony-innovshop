<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Product;
use App\Form\CategoryType;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontController extends AbstractController
{

    #[Route('/', name: 'app_front')] // Nom de la route et le / doivent être uniques dans tte l'application
    public function index(PostRepository $postRepository): Response
    {

        $posts = $postRepository->findLastPosts();

        return $this->render('front/index.html.twig', [
            'posts' => $posts
        ]);
    }


    #[Route('/contact', name: 'app_front_contact')]
    public function contact(): Response
    {
        return $this->render('front/contact.html.twig', [

        ]);
    }

    #[Route('/catalogue', name: 'app_front_catalogue')]
    public function catalogue(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {


        $query = $productRepository->findAll();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10);/*limit per page*/

        return $this->render('front/catalogue.html.twig', ['products' => $pagination

        ]);
    }

    #[Route('/catalogue/{id}', name: 'app_front_catalogue_detail')]
    public function catalogueDetail(Product $product): Response
    {

        return $this->render('front/catalogue_detail.html.twig', ['product' => $product

        ]);
    }

    #[Route('/category/{id}', name: 'category_show')]
    public function categoryShow(Category $category): Response
    {

        return $this->render('front/category_show.html.twig', ['category' => $category

        ]);
    }

    #[Route('/actualites', name: 'app_front_actu')]
    public function actu(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        return $this->render('front/posts.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/dislike/{id}', name: 'user_dislike')]
    public function dislike(Comment $comment, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $avis = $comment->getAvis();
        $avi = null;

        $isAlreadyAct = false;

        foreach ($avis as $avi){
            if($avi->getUser() == $user){
                $avi->setLiked(false);
                $isAlreadyAct = true;
            }
        }
        if($isAlreadyAct == false){
            $avi = new Avis();
            $avi->setLiked(false)->setUser($user)->setComments($comment);
        }
        $em->persist($avi);
        $em->flush();

        return $this->redirectToRoute('app_front_actu_detail', ['id'=> $comment->getPosts()->getId()]);

    }

    #[Route('/like/{id}', name: 'user_like')]
    public function like(Comment $comment, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $avis = $comment->getAvis();
        $avi = null;

        $isAlreadyAct = false;

        foreach ($avis as $avi){
            if($avi->getUser() == $user){
                $avi->setLiked(true);
                $isAlreadyAct = true;
            }
        }
        if($isAlreadyAct == false){
            $avi = new Avis();
            $avi->setLiked(true)->setUser($user)->setComments($comment);
        }
        $em->persist($avi);
        $em->flush();

        return $this->redirectToRoute('app_front_actu_detail', ['id'=> $comment->getPosts()->getId()]);


    }

    #[Route('/actualites/{id}', name: 'app_front_actu_detail')]
    public function actuDetail(PostRepository $postRepository, $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->findOneBy(['id' => $id]);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $post = $postRepository->findOneBy(['id' => $id]);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPosts($post)->setAuthor($user)->setValid(false);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_front_actu_detail', ['id' => $id]);
        }



        return $this->render('front/actu_detail.html.twig',
            [
                'post' => $post,
                'form' => $form,

            ]);

    }

}
