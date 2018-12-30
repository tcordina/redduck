<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\SubCategory;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->findAll();
        return $this->render('default/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/setadmin/{user}", name="grant_admin", methods="POST")
     */
    public function setAdmin(Request $request, User $user)
    {
        if ($this->isCsrfTokenValid('grant_admin', $request->get('_csrf_token'))) {
            $em = $this->getDoctrine()->getManager();
            if ($user->getRoles() === ['ROLE_ADMIN']) {
                $user->setRoles([]);
            } else {
                $user->setRoles(['ROLE_ADMIN']);
            }
            $em->persist($user);
            $em->flush();
        }
        return $this->redirectToRoute('user_show', [
            'username' => $user->getUsername(),
        ]);
    }

    /**
     * @Route("/setmoderator/{user}", name="grant_mod", methods="POST")
     */
    public function setModerator(Request $request, User $user)
    {
        if ($this->isCsrfTokenValid('grant_mod', $request->get('_csrf_token'))) {
            $em = $this->getDoctrine()->getManager();
            if ($user->getRoles() === ['ROLE_MODERATOR']) {
                $user->setRoles([]);
            } else {
                $user->setRoles(['ROLE_MODERATOR']);
            }
            $em->persist($user);
            $em->flush();
        }
        return $this->redirectToRoute('user_show', [
            'username' => $user->getUsername(),
        ]);
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request)
    {
        $query = $request->query->get('search');
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        /**
         * @var $qb QueryBuilder
         */
        $categories = $qb
            ->select('c')
            ->from(Category::class, 'c')
            ->where('c.name LIKE :query')
            ->orWhere('c.slug LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $subcategories = $qb
            ->select('s')
            ->from(SubCategory::class, 's')
            ->where('s.name LIKE :query')
            ->orWhere('s.slug LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $posts = $qb
            ->select('p')
            ->from(Post::class, 'p')
            ->leftJoin('p.author', 'a')
            ->where('p.title LIKE :query')
            ->orWhere('a.username LIKE :query')
            ->orWhere('p.slug LIKE :query')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $users = $qb
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.username LIKE :query')
            ->orderBy('u.username', 'ASC')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $resultsCount = count($categories) + count($subcategories) + count($posts) + count($users);

        return $this->render('default/searchresult.html.twig', [
            'categories' => $categories,
            'subcategories' => $subcategories,
            'posts' => $posts,
            'users' => $users,
            'searchQuery' => $query,
            'resultsCount' => $resultsCount,
        ]);
    }
}
