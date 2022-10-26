<?php

namespace App\Controller;

use App\Entity\Comentario;
use App\Entity\Post;
use App\Form\ComentarioType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    #[Route('/post', name: 'post')]
    public function index(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $formulario = $this -> createForm(PostType::class, $post);
        $formulario -> handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()){
            // Obtenemos al Usuario
            $usuario = $this -> getUser();
            $post -> setUsuario($usuario);

            /** @var UploadedFile $fotoArchivo */
            $fotoArchivo = $formulario->get('foto')->getData();
            if ($fotoArchivo) {
                $fotoOriginal = pathinfo($fotoArchivo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($fotoOriginal);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$fotoArchivo->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $fotoArchivo->move(
                        $this->getParameter('foto_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Ocurrio un error al cargar la FOTO');
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setFoto($newFilename);
            }

            $entityManager -> persist($post);
            $entityManager -> flush();

            return $this -> redirectToRoute('principal');
        }
        return $this->render('post/index.html.twig', [
            'formulario' => $formulario-> createView(),
        ]);
    }

    #[Route('/post/{id}', name:'verPost')]
    public function verPost($id, EntityManagerInterface $entityManager, Request $request){

        $post = $entityManager->getRepository(Post::class)->find($id);

        $comentario = new Comentario();
        $consultaComentario = $entityManager->getRepository(Comentario::class)->BuscarComentarioUnPost($post->getId());

        $formulario = $this->createForm(ComentarioType::class, $comentario);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()){
            $usuario = $this->getUser();
            $comentario->setPost($post);
            $comentario->setUsuario($usuario);
            $entityManager->persist($comentario);
            $entityManager->flush();

            $this->addFlash('success', Comentario::COMENTARIO_AGREGADO_EXITOSAMENTE);
            return $this->redirectToRoute('verPost',[
               'id' => $post->getId()
            ]);
        }

        return $this->render('post/verPost.html.twig',[
            'post'=>$post,
            'formulario' => $formulario->createView(),
            'comentarios' => $consultaComentario
        ]);
    }

    #[Route('/mi_post', name:'miPost')]
    public function miPost(EntityManagerInterface $entityManager){

        $usuario = $this->getUser();
        $post = $entityManager->getRepository(Post::class)->findBy(['usuario'=>$usuario]);

        return $this->render('post/miPost.html.twig',[
            'post' => $post
        ]);
    }
}
