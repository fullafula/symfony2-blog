<?php
// src/Blogger/BlogBundle/Controller/BlogController.php

namespace Blogger\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Blogger\BlogBundle\Entity\Blog;

/**
 * Blog controller.
 */
class BlogController extends Controller
{
    /**
     * Show a blog entry
     */
    public function showAction($id, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository('BloggerBlogBundle:Blog')->find($id);

        if (!$blog) {
            throw $this->createNotFoundException('Unable to find Blog post.');
        }

	      $comments = $em->getRepository('BloggerBlogBundle:Comment')
                   ->getCommentsForBlog($blog->getId());

        return $this->render('BloggerBlogBundle:Blog:show.html.twig', array(
            'blog'      => $blog,
	          'comments'  => $comments
        ));
    }

    public function newAction(Request $request)
    {
        $form = $this->createFormBuilder(new Blog())
            ->add('title')
            ->add('author')
            ->add('blog')
            ->add('image')
            ->add('tags')
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $blog = $form->getData();
                $blog->setCreated(new \DateTime());
                $blog->setUpdated(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($blog);
                $em->flush();

                return $this->redirect($this->generateUrl('BloggerBlogBundle_homepage'));
            }
        }
        return $this->render('BloggerBlogBundle:Blog:new.html.twig', array(
            'blog' => $blog,
            'form' => $form->createView()
        ));
    }

    public function deleteAction($id)
    {
        // var_dump($post);
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository('BloggerBlogBundle:Blog')->find($id);
        if (!$blog) {
            throw $this->createNotFoundException('The post does not exist');
        }
        // delete
        $em->remove($blog);
        $em->flush();

        return $this->redirect($this->generateUrl('BloggerBlogBundle_homepage'));
    }

    public function editAction($id)
    {
        // DBから取得
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository('BloggerBlogBundle:Blog')->find($id);
        if (!$blog) {
            throw new NotFoundHttpException('The post does not exist.');
        }

        // フォームのビルド
        $form = $this->createFormBuilder($blog)
            ->add('title')
            ->add('author')
            ->add('blog')
            ->add('image')
            ->add('tags')
            ->getForm();

        // バリデーション
        $request = $this->getRequest();
        if ('POST' === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                // 更新されたエンティティをデータベースに保存
                $blog = $form->getData();
                $blog->setUpdated(new \DateTime());
                $em->flush();
                return $this->redirect($this->generateUrl('BloggerBlogBundle_homepage'));
            }
        }

        // 描画
        return $this->render('BloggerBlogBundle:Blog:edit.html.twig', array(
            'blog' => $blog,
            'form' => $form->createView(),
        ));
    }
}
