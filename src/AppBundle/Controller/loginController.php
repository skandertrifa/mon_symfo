<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class loginController extends Controller
{
    /**
     * @Route("/", name="login")
     */
    public function indexAction (Request $request ){
        $this->getDoctrine();

    }
}
