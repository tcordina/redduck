<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Post;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/message")
 */
class MessageController extends AbstractController
{
    /*
     * @Route("/", name="message_index", methods="GET")
     */
    public function index(MessageRepository $messageRepository): Response
    {
        return $this->render('message/index.html.twig', ['messages' => $messageRepository->findAll()]);
    }

    public function listByPost(Post $post, MessageRepository $messageRepository)
    {
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findBy([
                'post' => $post,
            ])
        ]);
    }

    /**
     * @Route("/new/{post}", name="message_new", methods="GET|POST")
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request, Post $post): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $message->setPost($post)->setAuthor($this->getUser());
            $em->persist($message);
            $em->flush();

            return $this->redirectToRoute('post_show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug(),
            ]);
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="message_show", methods="GET")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', ['message' => $message]);
    }

    /**
     * @Route("/{id}/edit", name="message_edit", methods="GET|POST")
     * @Security("is_granted('MESSAGE_EDIT', message)")
     */
    public function edit(Request $request, Message $message): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setPost($message->getPost());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_show', [
                'id' => $message->getPost()->getId(),
                'slug' => $message->getPost()->getSlug(),
            ]);
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="message_delete", methods="DELETE")
     * @Security("is_granted('MESSAGE_DELETE', message)")
     */
    public function delete(Request $request, Message $message): Response
    {
        $post = $message->getPost();
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($message);
            $em->flush();
        }

        return $this->redirectToRoute('post_show', [
            'id' => $post->getId(),
            'slug' => $post->getSlug(),
        ]);
    }

    /**
     * @Route("/{id}/upvote", name="message_upvote", methods="POST")
     */
    public function upvote(Message $message)
    {
        if (!$this->getUser() instanceof UserInterface) {
            return new Response('login');
        }
        $em = $this->getDoctrine()->getManager();
        $author = $message->getAuthor();
        if ($message->getUpvotes()->contains($this->getUser())) {
            $message->removeUpvote($this->getUser());
            $message->setKarma($message->getKarma() - 1);
            $author->setKarma($author->getKarma() - 1);
            $em->persist($message);
            $em->persist($author);
            $em->flush();
            return new Response('removed');
        }
        if ($message->getDownvotes()->contains($this->getUser())) {
            $message->removeDownvote($this->getUser());
            $message->setKarma($message->getKarma() + 1);
            $author->setKarma($author->getKarma() + 1);
        }
        $message->addUpvote($this->getUser());
        $message->setKarma($message->getKarma() + 1);
        $author->setKarma($author->getKarma() + 1);
        $em->persist($message);
        $em->persist($author);
        $em->flush();
        return new Response('added');
    }

    /**
     * @Route("/{id}/downvote", name="message_downvote", methods="POST")
     */
    public function downvote(Message $message)
    {
        if (!$this->getUser() instanceof UserInterface) {
            return new Response('login');
        }
        $em = $this->getDoctrine()->getManager();
        $author = $message->getAuthor();
        if ($message->getDownvotes()->contains($this->getUser())) {
            $message->removeDownvote($this->getUser());
            $message->setKarma($message->getKarma() + 1);
            $author->setKarma($author->getKarma() + 1);
            $em->persist($message);
            $em->persist($author);
            $em->flush();
            return new Response('removed');
        }
        if ($message->getUpvotes()->contains($this->getUser())) {
            $message->removeUpvote($this->getUser());
            $message->setKarma($message->getKarma() - 1);
            $author->setKarma($author->getKarma() - 1);
        }
        $message->addDownvote($this->getUser());
        $message->setKarma($message->getKarma() - 1);
        $author->setKarma($author->getKarma() - 1);
        $em->persist($message);
        $em->persist($author);
        $em->flush();
        return new Response('added');
    }
}
