<?php

namespace ArticleBundle\Controller;

use AppBundle\Entity\User;
use ArticleBundle\Entity\Article;
use ArticleBundle\Form\ArticleType;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('@Article\Default\index2.html.twig');
    }


    // Ajouter un article
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
         $arcticle = new Article();
         $arcticle->addUser($this->getUser());
        $form = $this->createForm(ArticleType::class,$arcticle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $arcticle = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
             $entityManager = $this->getDoctrine()->getManager();
             $entityManager->persist($arcticle);
             $entityManager->flush();
            // redirection for sucess message !!!!!!!!!!
        }


        return $this->render('@Article/Default/index.html.twig',
            array(
                'form' => $form->createView()
                )
        );

    }


    //Lister les articles
    public function listAction ($user)
    {



        //search the user associated !
        //****************
        /*
        $repository = $this->getDoctrine()->getRepository(User::class);
        $utilisateur = $repository->findOneById($user_id);
        dump($utilisateur);
        //*********************/


         //$repo = $this->getDoctrine()->getRepository(Article::class);
         //$rep = $repo->findByUser ($utilisateur);
        /* $query = $repo->createQueryBuilder('p')
            ->where('p.user > :user')
            ->setParameter('user', '7')
            //->orderBy('p.price', 'ASC')
            ->getQuery();
        $products = $query->getResult();*/


       /* $entityManager = $this->getDoctrine()->getManager();
        $rsm = new ResultSetMapping();
        $query = $entityManager->createNativeQuery(
            'SELECT id, title, code, date_release, description, price, author from mon_symfo.article WHERE id IN (SELECT article_id FROM mon_symfo.article_user WHERE user_id=7)',$rsm);
        // $query->setParameter(1,7);
        $rep = $query->getResult();

        dump($rep);*/

        $user_id = $user;
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $u = $this->getUser();

        if ($u->getId() == $user_id ) {
        $entityManager = $this->getDoctrine()->getManager();
        $conn = $entityManager->getConnection();
        $sql = 'SELECT id, title, code, date_release, description, price, author from mon_symfo.article WHERE id IN (SELECT article_id FROM mon_symfo.article_user WHERE user_id=?)';
        //$sql->bindValue(1,$user_id);
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($user_id));
        $rep = $stmt->fetchAll();
        }
        else {
            $rep = null;
        }
         return $this->render('@Article/show.html.twig',array(
                'articles' => $rep,
                'user_id' => $user_id
    ));

    }
    public function searchAction(Request $request)
    {
        // search value here : $request->request->get('search');
        $x = $request->request->get('search');

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $u = $this->getUser();
        $user_id = $u->getId();
        $entityManager = $this->getDoctrine()->getManager();
        $conn = $entityManager->getConnection();
        $sql = 'SELECT id, title, code, date_release, description, price, author from mon_symfo.article WHERE title=? AND id IN (SELECT article_id FROM mon_symfo.article_user WHERE user_id=?)';
        //$sql->bindValue(1,$user_id);
        $stmt = $conn->prepare($sql);
        $stmt->execute(array ($x,$user_id) );
        $rep = $stmt->fetchAll();

        return $this->render('@Article/show.html.twig',array(
            'articles' => $rep,
            'user_id' => $user_id));

    }
    public function deleteAction (Request $request,$id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED',null,'access refusé');
        $rp = $this->getDoctrine()->getRepository(Article::class);
        $article = $rp->findOneById($id);
            if ($article == null ) {
                throw $this->createNotFoundException('Article non trouve');
            }
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        $u = $this->getUser();
        $user_id = $u->getId();
        return $this->redirectToRoute('homepage');

    }
}
