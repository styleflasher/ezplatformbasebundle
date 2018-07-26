<?php
namespace Styleflasher\eZPlatformBaseBundle\Controller;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class DoNotTrackController
{

    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    protected $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     *
     * @param View $view
     * @param Request $request
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    public function isDoNotTrackEnabledAction(View $view, Request $request)
    {
        $view->addParameters(
            [
                'doNotTrack' => $this->isDoNotTrackEnabled($request)
            ]
        );
        return $view;
    }

    /**
     *
     * @param string $template
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function isDoNotTrackEnabledTemplateAction($template, Request $request)
    {
        $response = new Response();
        return $this->templating->renderResponse(
            $template,
            [
                'doNotTrack' => $this->isDoNotTrackEnabled($request)
            ],
            $response
        );
    }

    /**
     *
     * @param Request $request
     * @return boolean
     */
    protected function isDoNotTrackEnabled(Request $request)
    {
        $doNotTrack  = $request->headers->get('DNT', 0);
        return $doNotTrack === "1";
    }
}
