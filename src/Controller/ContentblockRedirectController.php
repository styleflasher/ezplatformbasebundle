<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Depth;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContentblockRedirectController
{
    public function __construct(
        LocationService $locationService,
        SearchService $searchService,
        URLAliasService $urlAliasService
    ) {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->urlAliasService = $urlAliasService;

        return $this;
    }

    public function redirectToParentAction(ContentView $view)
    {
        $currentLocation = $view->getLocation();
        $contentTypeIdentifiers = $view->getParameter('displayableContentTypeIdentifiers');

        $displayableLocationSearchResult = $this->fetchDisplayableLocation($currentLocation, $contentTypeIdentifiers);
        $displayableLocation = $displayableLocationSearchResult->searchHits[0]->valueObject;

        $redirectUrlAlias = $this->urlAliasService->reverseLookup($displayableLocation);
        $pathArray = explode("/", $redirectUrlAlias->path);
        unset($pathArray[0], $pathArray[1]);
        $path = '/' . implode("/", $pathArray);

        return new RedirectResponse(
            $path,
            302
        );
    }

    protected function fetchDisplayableLocation(Location $location, $contentTypeIdentifiers)
    {
        $pathArray = $location->path;

        $criteria = [
            new ContentTypeIdentifier($contentTypeIdentifiers),
            new Visibility(Visibility::VISIBLE),
            new LocationId($pathArray)
            
        ];

        $query = new LocationQuery();
        $query->query = new LogicalAnd($criteria);
        $query->sortClauses = [
            new Depth($query::SORT_DESC)
        ];

        $query->limit = 1;

        $callToActionSearchResult = $this->searchService->findLocations($query);
        if ($callToActionSearchResult->totalCount === 0) {
            return false;
        }

        return $callToActionSearchResult;
    }
}
