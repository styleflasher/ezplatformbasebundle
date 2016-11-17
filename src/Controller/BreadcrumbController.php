<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\API\Repository\LocationService;

class BreadcrumbController
{

    protected $templating;
    protected $locationService;
    protected $configResolver;

    public function __construct(
        $templating,
        LocationService $locationService,
        $configResolver
    ) {
        $this->templating = $templating;
        $this->locationService = $locationService;
        $this->configResolver = $configResolver;
    }

    public function renderBreadcrumbAction(
        $locationId,
        $template = 'StyleflashereZPlatformBaseBundle:components:breadcrumb.html.twig'
    ) {

        $location =  $this->locationService->loadLocation( $locationId );
        $pathArray = explode('/', $location->pathString);
        array_shift($pathArray);
        array_pop($pathArray);

        foreach($pathArray as $key => $locationId){
            if($locationId == $this->configResolver->getParameter( 'content.tree_root.location_id' )) {
                break;
            }
            unset($pathArray[$key]);
        }

        $breadcrumbItems = [];
        foreach ($pathArray as $pathItem) {
            $breadcrumbItems[] = $this->locationService->loadLocation( $pathItem );
        }

        return $this->templating->renderResponse(
            $template, [
                'breadcrumbItems' => $breadcrumbItems,
            ], new Response()
        );
    }
}
