<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class  RegistroController extends AbstractController
{
    #[Route('/registro', name: 'registro')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = new Usuario();
        $formulario = $this->createForm(UsuarioType::class, $usuario);
        $formulario -> handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()){

            // Encripatamos el campo contraseña
            $encriptarContraseña = $passwordHasher->hashPassword(
                $usuario, $formulario['password']->getData()
            );
            $usuario->setPassword($encriptarContraseña);

            // Persistir los datos del formulario
            $entityManager -> persist($usuario);
            $entityManager -> flush();

            // Mensajes Flush
            $this->addFlash('success', Usuario::REGISTRO_EXITOSO);

            return $this->redirectToRoute('registro');
        }
        
        return $this->render('registro/index.html.twig', [
            'controller_name' => 'Controlador para el Registro',
            'formulario' => $formulario -> createView()
        ]);
    }
}
