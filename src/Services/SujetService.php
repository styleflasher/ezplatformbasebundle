<?php

namespace Styleflasher\eZPlatformBaseBundle\Services;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;

class SujetService
{
    protected $locationService;
    protected $contentService;
    protected $searchService;
    protected $configResolver;
    protected $sortClauseService;

    public function __construct(
        LocationService $locationService,
        ContentService $contentService,
        SearchService $searchService,
        SortClauseService $sortClauseService,
        $configResolver
    ) {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->searchService = $searchService;
        $this->configResolver = $configResolver;
        $this->sortClauseService = $sortClauseService;
    }

    public function getSujets( $location ) {
        return $this->fetchSujets($location);
    }

    protected function fetchSujets( $location ) {
        $query = $this->buildQuery($location);
        $searchResults = $this->searchService->findLocations($query);

        if (sizeof($searchResults->searchHits)) {
            return $this->getResultArray($searchResults->searchHits);
        }

        if ($this->configResolver->getParameter( 'content.tree_root.location_id' ) != $location->id) {
            $parentLocation = $this->locationService->loadLocation($location->parentLocationId);
            return $this->fetchSujets($parentLocation);
        }

        $fallbackLocation = $this->locationService->loadLocation($this->configResolver->getParameter('sujets.fallback_container_location_id', 'styleflashere_z_platform_base'));
        return $this->fetchSujets($fallbackLocation);
    }

    protected function buildQuery($location) {
        $classIdentifier = $this->configResolver->getParameter('sujets.sujetclasses', 'styleflashere_z_platform_base');

        $criteria = [
            new ContentTypeIdentifier($classIdentifier),
            new Visibility(Visibility::VISIBLE),
            new ParentLocationId([ $location->id ])
        ];

        $query = new LocationQuery();
        $query->filter = new LogicalAnd($criteria);
        $query->sortClauses = $this->sortClauseService->generateSortClause($location->sortField, $location->sortOrder);

        return $query;
    }

    protected function getResultArray($searchResults) {
        $sujets = [];

        foreach ($searchResults as $searchResult) {
            $resultLocation = $searchResult->valueObject;
            $resultContent = $this->contentService->loadContent($resultLocation->contentInfo->id);
            $sujets[] = array(
                'location' => $resultLocation,
                'content' => $resultContent
            );
        }

        return $sujets;
    }
}
