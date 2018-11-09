<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/setadmin")
     */
    public function setAdmin()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $user->setRoles(['ROLE_ADMIN']);
        $em->persist($user);
        $em->flush();
        return new Response('Done');
    }
}
