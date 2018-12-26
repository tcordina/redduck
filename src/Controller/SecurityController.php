<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="security_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/register", name="security_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user)->remove('imageFile');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPlainpassword());
            $user->setPassword($password);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            try {
                $em->flush();
                $this->addFlash('success', 'Registration success. You can now sign in !');
                return $this->redirectToRoute('security_login');
            } catch(DBALException $e) {
                $this->addFlash('danger', 'An error occured, please try again.');
                return $this->redirectToRoute('security_register');
            }
        }
        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
        throw new AccessDeniedHttpException('What u doing here ?');
    }
}
