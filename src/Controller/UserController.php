<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/u")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods="GET")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', ['users' => $userRepository->findAll()]);
    }

    /**
     * @Route("/{username}", name="user_show", methods="GET")
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{username}/edit", name="user_edit", methods="GET|POST")
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {
        if ($this->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(UserType::class, $request->get('user'))->add('bio');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (trim($user->getPlainpassword()) != '') {
                $password = $encoder->encodePassword($user, $user->getPlainpassword());
                $user->setPassword($password);
            }
            $user->setUpdatedAt(new \DateTime('now'));
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'Profile successfully updated.');
                return $this->redirectToRoute('user_show', [
                    'username' => $user->getUsername(),
                ]);
            } catch (DBALException $e) {
                $this->addFlash('danger', 'An error occured. Try again later.');
                return $this->redirectToRoute('user_show', [
                    'username' => $user->getUsername(),
                ]);
            }
        }

        return $this->render('user/edit.html.twig', ['user' => $user,
            'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods="DELETE")
     */
    public
    function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
