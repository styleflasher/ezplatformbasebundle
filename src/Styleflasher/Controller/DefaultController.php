<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('StyleflashereZPlatformBaseBundle:Default:index.html.twig', array('name' => $name));
    }
}
