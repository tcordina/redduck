<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\SubCategory;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\SluggerService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/p")
 */
class PostController extends AbstractController
{
    /*
     * @Route("/", name="post_index", methods="GET")
     */
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', ['posts' => $postRepository->findAll()]);
    }

    /**
     * @Route("/new/{subcategory}", name="post_new", methods="GET|POST")
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request, SubCategory $subcategory, SluggerService $sluggerService): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post)->remove('subcategory');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $sluggerService->slugify($post->getTitle());
            $ytlink = $this->getYtLink($post->getContent());
            $em = $this->getDoctrine()->getManager();
            $post->setSubcategory($subcategory)
                ->setAuthor($this->getUser())
                ->setSlug($slug)
                ->setYtlink($ytlink);
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('subcategory_show', [
                'slug' => $subcategory->getSlug(),
            ]);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'subcategory' => $subcategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/{id}", name="post_show", methods="GET")
     */
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/{id}/{slug}/edit", name="post_edit", methods="GET|POST")
     * @Security("is_granted('POST_EDIT', post)")
     */
    public function edit(Request $request, Post $post, SluggerService $sluggerService): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $sluggerService->slugify($post->getTitle());
            $ytlink = $this->getYtLink($post->getContent());
            $em = $this->getDoctrine()->getManager();
            $post->setSlug($slug)->setYtlink($ytlink);
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('post_show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug(),
            ]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods="DELETE")
     * @Security("is_granted('POST_DELETE', post)")
     */
    public function delete(Request $request, Post $post): Response
    {
        $subcategory = $post->getSubcategory();
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('subcategory_show', [
            'slug' => $subcategory->getSlug(),
        ]);
    }

    /**
     * @Route("/{id}/upvote", name="post_upvote", methods="POST")
     */
    public function upvote(Post $post)
    {
        if (!$this->getUser() instanceof UserInterface) {
            return new Response('login');
        }
        $em = $this->getDoctrine()->getManager();
        $author = $post->getAuthor();
        if ($post->getUpvotes()->contains($this->getUser())) {
            $post->removeUpvote($this->getUser());
            $post->setKarma($post->getKarma() - 1);
            $author->setKarma($author->getKarma() - 1);
            $em->persist($post);
            $em->persist($author);
            $em->flush();
            return new Response('removed');
        }
        if ($post->getDownvotes()->contains($this->getUser())) {
            $post->removeDownvote($this->getUser());
            $post->setKarma($post->getKarma() + 1);
            $author->setKarma($author->getKarma() + 1);
        }
        $post->addUpvote($this->getUser());
        $post->setKarma($post->getKarma() + 1);
        $author->setKarma($author->getKarma() + 1);
        $em->persist($post);
        $em->persist($author);
        $em->flush();
        return new Response('added');
    }

    /**
     * @Route("/{id}/downvote", name="post_downvote", methods="POST")
     */
    public function downvote(Post $post)
    {
        if (!$this->getUser() instanceof UserInterface) {
            return new Response('login');
        }
        $em = $this->getDoctrine()->getManager();
        $author = $post->getAuthor();
        if ($post->getDownvotes()->contains($this->getUser())) {
            $post->removeDownvote($this->getUser());
            $post->setKarma($post->getKarma() + 1);
            $author->setKarma($author->getKarma() + 1);
            $em->persist($post);
            $em->persist($author);
            $em->flush();
            return new Response('removed');
        }
        if ($post->getUpvotes()->contains($this->getUser())) {
            $post->removeUpvote($this->getUser());
            $post->setKarma($post->getKarma() - 1);
            $author->setKarma($author->getKarma() - 1);
        }
        $post->addDownvote($this->getUser());
        $post->setKarma($post->getKarma() - 1);
        $author->setKarma($author->getKarma() - 1);
        $em->persist($post);
        $em->persist($author);
        $em->flush();
        return new Response('added');
    }

    private function getYtLink(?string $content): ?string
    {
        preg_match_all('@(https?://)?(?:www\.)?(youtu(?:\.be/([-\w]+)|be\.com/watch\?v=([-\w]+)))\S*@im', $content, $aMatches);
        if (isset($aMatches[0][0])) {
            $ytlink = explode('?v=', $aMatches[0][0])[1];
        } else {
            $ytlink = null;
        }
        return $ytlink;
    }
}
