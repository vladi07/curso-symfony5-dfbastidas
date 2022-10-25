<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post', name: 'post')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $formulario = $this -> createForm(PostType::class, $post);

        $formulario -> handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()){
            // Obtenemos al Usuario
            $usuario = $this -> getUser();
            $post -> setUsuario($usuario);

            $entityManager -> persist($post);
            $entityManager -> flush();

            return $this -> redirectToRoute('principal');
        }
        return $this->render('post/index.html.twig', [
            'controller_name' => 'Controlador del Post',
            'formulario' => $formulario-> createView(),
        ]);
    }
}
