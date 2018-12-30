<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use App\Repository\PostRepository;
use App\Repository\SubCategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/r")
 */
class SubCategoryController extends AbstractController
{
    /*
     * @Route("/", name="subcategory_index", methods="GET")
     */
    public function index(SubCategoryRepository $subCategoryRepository): Response
    {
        return $this->render('subcategory/index.html.twig', ['subcategories' => $subCategoryRepository->findAll()]);
    }

    /**
     * @Route("/new/{category}", name="subcategory_new", methods="GET|POST")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request, Category $category): Response
    {
        $subCategory = new SubCategory();
        $form = $this->createForm(SubCategoryType::class, $subCategory)->remove('category');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = str_replace(' ', '-', $subCategory->getName());
            $slug = str_replace('\'', '', $slug);
            $subCategory
                ->setSlug(strtolower($slug))
                ->setCategory($category);
            $em = $this->getDoctrine()->getManager();
            $em->persist($subCategory);
            $em->flush();

            return $this->redirectToRoute('category_show', [
                'slug' => $category->getSlug(),
            ]);
        }

        return $this->render('subcategory/new.html.twig', [
            'subcategory' => $subCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="subcategory_show", methods="GET")
     */
    public function show(Request $request, SubCategory $subCategory, PostRepository $postRepository): Response
    {
        $sort = strtolower(trim($request->query->get('sort')));
        if ($sort === 'hot') {
            $posts = $postRepository->findByHot($subCategory);
        } elseif ($sort === 'top') {
            $posts = $postRepository->findByTop($subCategory);
        } else {
            $posts = array_reverse($postRepository->findBy(['subcategory' => $subCategory]));
        }
        return $this->render('subcategory/show.html.twig', [
            'subcategory' => $subCategory,
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="subcategory_edit", methods="GET|POST")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, SubCategory $subCategory): Response
    {
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = str_replace(' ', '-', $subCategory->getName());
            $slug = str_replace('\'', '', $slug);
            $subCategory->setSlug(strtolower($slug));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('subcategory_show', ['slug' => $subCategory->getSlug()]);
        }

        return $this->render('subcategory/edit.html.twig', [
            'subcategory' => $subCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="subcategory_delete", methods="DELETE")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, SubCategory $subCategory): Response
    {
        $category = $subCategory->getCategory();
        if ($this->isCsrfTokenValid('delete'.$subCategory->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($subCategory);
            $em->flush();
        }

        return $this->redirectToRoute('category_show', [
            'slug' => $category->getSlug(),
        ]);
    }
}
