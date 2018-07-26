<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\Core\MVC\Symfony\Routing\ChainRouter;
use Symfony\Cmf\Component\Routing\ChainRouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectController
{

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        SearchService $searchService,
        ChainConfigResolver $configResolver,
        ChainRouterInterface $router
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->configResolver = $configResolver;
        $this->router = $router;

        return $this;
    }

    public function redirectToDefinedLocation(GetResponseEvent $event)
    {
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            $request = $event->getRequest();
            $locationId = $request->attributes->get('locationId');
            $requestAttributes = $request->attributes;
            $siteaccess = $requestAttributes->get('siteaccess')->name;

            if ($siteaccess != 'admin' && $locationId != null) {
                $currentLocation = $this->locationService->loadLocation($locationId);
                $currentContent = $this->contentService->loadContentByContentInfo($currentLocation->contentInfo);
                $redirectToChild = $currentContent->getFieldValue('redirect_to_child');
                $redirectTo = $currentContent->getFieldValue('redirect_to');

                if (array_key_exists('redirect_to', $currentContent->fields) &&
                    ($currentContent->getFieldValue('redirect_to')->destinationContentId != null) &&
                    ($currentContent->getFieldValue('redirect_to')->destinationContentId != '')) {
                    $redirectContent = $this->contentService->loadContent($redirectTo);
                    $this->redirectByContent($redirectContent, $event);
                } elseif (array_key_exists('redirect_to_child', $currentContent->fields) && $redirectToChild == '1') {
                    $this->redirectToFirstChild($event);
                }
            }
        }
    }

    public function redirectToFirstChild(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $locationId = $request->attributes->get('locationId');
        $location = $this->locationService->loadLocation($locationId);
        $parentLocation = $this->locationService->loadLocation($location->parentLocationId);

        $redirectCandidates = $this->getRedirectToChildrenCandidates($location->contentInfo);

        $criteria = [
            new Visibility(Visibility::VISIBLE),
            new ParentLocationId($location->id)
        ];

        if (!empty($redirectCandidates)) {
            $criteria[] = new ContentTypeIdentifier($redirectCandidates);
        }

        $query = new LocationQuery();
        $query->query = new LogicalAnd($criteria);
        $query->sortClauses = $parentLocation->getSortClauses();

        $candidatesResult = $this->searchService->findLocations($query);

        if (count($candidatesResult->searchHits) > 0) {
            $this->redirectByLocation($candidatesResult->searchHits[0]->valueObject, $event);
        }
    }

    protected function getRedirectToChildrenCandidates(ContentInfo $contentInfo)
    {
        $candidates = $this->configResolver->getParameter('redirect_to_child', 'styleflashere_z_platform_base');

        if (!empty(count($candidates))) {
            $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);

            return $candidates[$contentType->identifier];
        }

        return [];
    }

    protected function redirectByLocation(Location $location, $event)
    {
        $path = $this->router->generate($location);

        $event->setResponse(
            new RedirectResponse(
                $path,
                302
            )
        );
        $event->stopPropagation();
    }

    protected function redirectByContent(Content $content, $event)
    {
        $location = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
        $path = $this->router->generate($location);

        $event->setResponse(
            new RedirectResponse(
                $path,
                302
            )
        );
        $event->stopPropagation();
    }
}
