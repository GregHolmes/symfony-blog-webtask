<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class BlogController extends Controller
{
    /** @var integer */
    const POST_LIMIT = 5;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $authorRepository;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $blogPostRepository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blogPostRepository = $entityManager->getRepository('App:BlogPost');
        $this->authorRepository = $entityManager->getRepository('App:Author');
    }

    /**
     * @Route("/", name="homepage")
     * @Route("/entries", name="entries")
     */
    public function entriesAction(Request $request)
    {
        $page = 1;

        if ($request->get('page')) {
            $page = $request->get('page');
        }

        return $this->render('Blog/entries.html.twig', [
            'blogPosts' => $this->blogPostRepository->getAllPosts($page, self::POST_LIMIT),
            'totalBlogPosts' => $this->blogPostRepository->getPostCount(),
            'page' => $page,
            'entryLimit' => self::POST_LIMIT
        ]);
    }

    /**
     * @Route("/entry/{slug}", name="entry")
     */
    public function entryAction($slug)
    {
        $blogPost = $this->blogPostRepository->findOneBySlug($slug);

        if (!$blogPost) {
            $this->addFlash('error', 'Unable to find entry!');

            return $this->redirectToRoute('entries');
        }

        return $this->render('Blog/entry.html.twig', array(
           'blogPost' => $blogPost
        ));
    }

    /**
     * @Route("/author/{name}", name="author")
     */
    public function authorAction($name)
    {
        $author = $this->authorRepository->findOneByUsername($name);

        if (!$author) {
            $this->addFlash('error', 'Unable to find author!');
            return $this->redirectToRoute('entries');
        }

        return $this->render('Blog/author.html.twig', [
            'author' => $author
        ]);
    }
}
