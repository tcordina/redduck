<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\SubCategory;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function setAdmin(Request $request, User $user, AuthorizationCheckerInterface $checker)
    {
        if ($this->isCsrfTokenValid('grant_admin', $request->get('_csrf_token'))) {
            $em = $this->getDoctrine()->getManager();
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
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
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function setModerator(Request $request, User $user, AuthorizationCheckerInterface $checker)
    {
        if ($this->isCsrfTokenValid('grant_mod', $request->get('_csrf_token'))) {
            $em = $this->getDoctrine()->getManager();
            if (in_array('ROLE_MODERATOR', $user->getRoles())) {
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
     * @Route(path="/grantadmin")
     */
    public function setadmindupauvre()
    {
        $this->getUser()->setRoles(['ROLE_ADMIN']);
        $this->getDoctrine()->getManager()->flush();

        return new Response('done');
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request)
    {
        $baseQuery = $request->query->get('search');
        $prefix = substr($baseQuery, 0, 2);
        $query = substr($baseQuery, 2);
        $em = $this->getDoctrine()->getManager();
        $categories = [];
        $subcategories = [];
        $posts = [];
        $users = [];
        switch ($prefix) {
            case 'c/':
                $categories = $em->getRepository(Category::class)->search($query);
                break;
            case 'r/':
                $subcategories = $em->getRepository(SubCategory::class)->search($query);
                break;
            case 'p/':
                $posts = $em->getRepository(Post::class)->search($query);
                break;
            case 'u/':
                $users = $em->getRepository(User::class)->search($query);
                break;
            default:
                $categories = $em->getRepository(Category::class)->search($query);
                $subcategories = $em->getRepository(SubCategory::class)->search($query);
                $posts = $em->getRepository(Post::class)->search($query);
                $users = $em->getRepository(User::class)->search($query);
        }

        $resultsCount = count($categories) + count($subcategories) + count($posts) + count($users);

        return $this->render('default/searchresult.html.twig', [
            'categories' => $categories,
            'subcategories' => $subcategories,
            'posts' => $posts,
            'users' => $users,
            'searchQuery' => $baseQuery,
            'resultsCount' => $resultsCount,
        ]);
    }
}
