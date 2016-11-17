<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\API\Repository\LocationService;
use Styleflasher\eZPlatformBaseBundle\Services\WidgetService;

class WidgetController
{

    protected $templating;
    protected $locationService;
    protected $widgetService;

    public function __construct(
        $templating,
        LocationService $locationService,
        WidgetService $widgetService
    ) {
        $this->templating = $templating;
        $this->locationService = $locationService;
        $this->widgetService = $widgetService;
    }

    public function renderWidgetsAction(
        $locationId,
        $template = 'StyleflashereZPlatformBaseBundle:components:widgets.html.twig'
    ) {

        $location =  $this->locationService->loadLocation( $locationId );
        $widgets = $this->widgetService->getWidgets($location);

        return $this->templating->renderResponse(
            $template, [
                'widgets' => $widgets,
            ], new Response()
        );
    }
}
