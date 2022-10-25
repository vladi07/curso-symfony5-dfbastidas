<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrincipalController extends AbstractController
{
    #[Route('/principal', name: 'principal')]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        /*$posts = $entityManager->getRepository(Post::class)->findAll();
        $posts1 = $entityManager->getRepository(Post::class)->find(1);
        $posts2 = $entityManager->getRepository(Post::class)->findOneBy(['titulo' => 'Como superar a tu ex']);
        $posts3 = $entityManager->getRepository(Post::class)->findBy(['likes'=>'']);*/

        $consulta = $entityManager->getRepository(Post::class)->BuscarTodosPost();

        $pagination = $paginator->paginate(
            $consulta, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            2 /*limit per page*/
        );


        return $this->render('principal/index.html.twig', [
            /*'controller_name' => 'Bienvenido a la Ventana Principal',
            'post' => $posts,
            'post1' => $posts1,
            'post2' => $posts2,
            'post3' => $posts3,

            'post4' => $post4,*/

            'pagination' => $pagination
        ]);
    }
}
