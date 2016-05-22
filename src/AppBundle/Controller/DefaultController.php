<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/** {@inheritDoc} */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('AppBundle:default:index.html.twig');
    }
}
