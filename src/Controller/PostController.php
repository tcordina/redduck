<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods="GET")
     */
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', ['posts' => $postRepository->findAll()]);
    }

    /**
     * @Route("/new", name="post_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = str_replace(' ', '-', $post->getTitle());
            $slug = str_replace('\'', '', $slug);
            $em = $this->getDoctrine()->getManager();
            $post->setAuthor($this->getUser())
                ->setSlug($slug)
                ->setCreatedAt(new \DateTime('now'))
                ->setUpdatedAt(null);
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{slug}", name="post_show", methods="GET")
     */
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/{id}/{slug}/edit", name="post_edit", methods="GET|POST")
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_index', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods="DELETE")
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('post_index');
    }

    /**
     * @Route("/{id}/upvote", name="post_upvote", methods="GET|POST")
     */
    public function upvote(Post $post)
    {
        if (!$this->getUser() instanceof UserInterface) {
            return new Response('login');
        }
        $em = $this->getDoctrine()->getManager();
        if ($post->getUpvotes()->contains($this->getUser())) {
            $post->removeUpvote($this->getUser());
            $em->persist($post);
            $em->flush();
            return new Response('removed');
        }
        if ($post->getDownvotes()->contains($this->getUser())) {
            $post->removeDownvote($this->getUser());
        }
        $post->addUpvote($this->getUser());
        $em->persist($post);
        $em->flush();
        return new Response('added');
    }

    /**
     * @Route("/{id}/downvote", name="post_downvote", methods="GET|POST")
     */
    public function downvote(Post $post)
    {
        if (!$this->getUser() instanceof UserInterface) {
            return new Response('login');
        }
        $em = $this->getDoctrine()->getManager();
        if ($post->getDownvotes()->contains($this->getUser())) {
            $post->removeDownvote($this->getUser());
            $em->persist($post);
            $em->flush();
            return new Response('removed');
        }
        if ($post->getUpvotes()->contains($this->getUser())) {
            $post->removeUpvote($this->getUser());
        }
        $post->addDownvote($this->getUser());
        $em->persist($post);
        $em->flush();
        return new Response('added');
    }
}
