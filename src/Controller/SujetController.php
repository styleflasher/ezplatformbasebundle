<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\TwigEngine;
use eZ\Publish\API\Repository\LocationService;
use Styleflasher\eZPlatformBaseBundle\Services\SujetService;

class SujetController
{

    protected $templating;
    protected $locationService;
    protected $sujetService;

    public function __construct(
        TwigEngine $templating,
        LocationService $locationService,
        SujetService $sujetService
    ) {
        $this->templating = $templating;
        $this->locationService = $locationService;
        $this->sujetService = $sujetService;
    }

    public function renderSujetsAction(
        $locationId,
        $template = 'StyleflashereZPlatformBaseBundle:components:sujet.html.twig'
    ) {

        $location =  $this->locationService->loadLocation( $locationId );
        $sujets = $this->sujetService->getSujets($location);

        return $this->templating->renderResponse(
            $template, [
                'sujets' => $sujets,
            ], new Response()
        );
    }
}
