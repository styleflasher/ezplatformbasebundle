<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class RedirectController {

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        SearchService $searchService,
        ContainerInterface $container,
        $configResolver
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->container = $container;
        $this->configResolver = $configResolver;

        return $this;
    }

    public function redirectToDefinedLocation(GetResponseEvent $event)
    {
        if ( $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST ) {
            $request = $event->getRequest();
            $locationId = $request->attributes->get('locationId');
            $requestAttributes = $request->attributes;
            $siteaccess = $requestAttributes->get('siteaccess')->name;

            if ($siteaccess != 'admin' && $locationId != NULL) {
                $currentLocation = $this->locationService->loadLocation( $locationId );
                $currentContent = $this->contentService->loadContentByContentInfo($currentLocation->contentInfo);

                if ( array_key_exists( 'redirect_to' , $currentContent->fields ) && ($currentContent->getFieldValue('redirect_to')->destinationContentId != NULL) && ($currentContent->getFieldValue('redirect_to')->destinationContentId != '')) {
                    $redirectContent = $this->contentService->loadContent($currentContent->getFieldValue('redirect_to_child'));
                    $this->redirectByContentInfo($redirectContent->contentInfo, $event);
                }
                elseif ( array_key_exists( 'redirect_to_child' , $currentContent->fields ) && $currentContent->getFieldValue('redirect_to_child') == '1') {
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
            new ContentTypeIdentifier($redirectCandidates),
            new Visibility(Visibility::VISIBLE),
            new ParentLocationId($location->id)
        ];

        $query = new LocationQuery();
        $query->query = new LogicalAnd($criteria);
        $query->sortClauses = $parentLocation->getSortClauses();

        $blogItemsResult = $this->searchService->findLocations($query);

        if(count($blogItemsResult->searchHits) > 0) {
            $this->redirectByContentInfo($blogItemsResult->searchHits[0]->valueObject->contentInfo, $event);
        }
    }

    protected function getRedirectToChildrenCandidates(ContentInfo $contentInfo)
    {
        $candidates = $this->configResolver->getParameter('redirect_to_child', 'styleflashere_z_platform_base');

        $countCandidates = count($candidates);

        if(intval($countCandidates) > 0) {
            $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);

            return $candidates[$contentType->identifier];
        }
    }

    protected function redirectByContentInfo(ContentInfo $contentInfo, $event) {
        $redirectLocation = $this->locationService->loadLocation($contentInfo->mainLocationId);
        $path = $this->container->get( 'router' )->generate( $redirectLocation );

        $event->setResponse(
            new RedirectResponse(
                $path,
                302
            )
        );
        $event->stopPropagation();
    }
}
