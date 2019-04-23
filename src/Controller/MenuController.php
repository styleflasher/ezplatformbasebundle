<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\API\Repository\LocationService;
use Styleflasher\eZPlatformBaseBundle\Services\MenuService;

class MenuController
{

    protected $templating;
    protected $locationService;
    protected $menuService;

    public function __construct(
        $templating,
        LocationService $locationService,
        MenuService $menuService
    ) {
        $this->templating = $templating;
        $this->locationService = $locationService;
        $this->menuService = $menuService;
    }

    public function renderMenuAction(
        $locationId,
        $template = 'StyleflashereZPlatformBaseBundle:components:topmenu.html.twig',
        $params = [],
        $returnArray = false
    ) {
        $location = $this->locationService->loadLocation($locationId);
        $menu = $this->menuService->generateTopmenu($location, $returnArray); // AS
        $path = explode('/', $location->pathString);
        array_shift($path);
        array_pop($path);

        return $this->templating->renderResponse(
            $template,
            [
                'menu' => $menu,
                'path' => $path,
                'location' => $location,
                'params' => $params
            ],
            new Response()
        );
    }
}
