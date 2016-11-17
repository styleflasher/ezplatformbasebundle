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
use Symfony\Bundle\TwigBundle\TwigEngine;

class WidgetService
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

    public function getWidgets( $location ) {
        $widgetConfiguration = $this->getWidgetConfiguration();
        $additionalMenuItems = $this->getAdditionalMenu($location, $widgetConfiguration);
        $additionalContent = $this->getAdditionalContent($location, $widgetConfiguration);

        $widgetContent = [
            'additionalMenuItems' => $additionalMenuItems,
            'additionalContent' => $additionalContent
        ];

        return $widgetContent;
    }

    protected function getWidgetConfiguration() {
        $widgetConfiguration = [
            'social_media' => [
                'show' => $this->configResolver->getParameter('right_column.social_media.show', 'styleflashere_z_platform_base'),
                'facebook' => $this->configResolver->getParameter('right_column.social_media.facebook', 'styleflashere_z_platform_base'),
                'twitter' => $this->configResolver->getParameter('right_column.social_media.twitter', 'styleflashere_z_platform_base'),
                'linkedin' => $this->configResolver->getParameter('right_column.social_media.linkedin', 'styleflashere_z_platform_base')
            ],
            'widgets' => [
                'show' => $this->configResolver->getParameter('right_column.widgets.show', 'styleflashere_z_platform_base'),
                'classes' => $this->configResolver->getParameter('right_column.widgets.classes', 'styleflashere_z_platform_base')
            ],
            'additional_menu' => [
                'show' => $this->configResolver->getParameter('right_column.additional_menu_level.show', 'styleflashere_z_platform_base'),
                'depth' => $this->configResolver->getParameter('right_column.additional_menu_level.depth', 'styleflashere_z_platform_base'),
                'classes' => $this->configResolver->getParameter('right_column.additional_menu_level.classes', 'styleflashere_z_platform_base'),
                'excluded_location_ids' => $this->configResolver->getParameter('right_column.additional_menu_level.excluded_location_ids', 'styleflashere_z_platform_base')
            ]
        ];

        return $widgetConfiguration;
    }

    protected function getAdditionalMenu($location, $widgetConfiguration) {
        if ($widgetConfiguration['additional_menu']['show']) {
            $depth = $widgetConfiguration['additional_menu']['depth'];
            $pathArray = explode('/', $location->pathString);
            array_shift($pathArray);
            array_pop($pathArray);
            if (isset($pathArray[$depth])) {
                $menuRootLocation =  $this->locationService->loadLocation( $pathArray[$depth] );
                $searchQuery = $this->buildQuery($menuRootLocation, $widgetConfiguration['additional_menu']['classes']);
                $searchResults = $this->searchService->findLocations($searchQuery);
                $additionalMenuItems = $this->getResultArray($searchResults->searchHits, $widgetConfiguration['additional_menu']['excluded_location_ids']);
                return $additionalMenuItems;
            }
        }
        return [];
    }

    protected function getAdditionalContent($location, $widgetConfiguration) {
        if ($widgetConfiguration['widgets']['show']) {
            $searchQuery = $this->buildQuery($location, $widgetConfiguration['widgets']['classes']);
            $searchResults = $this->searchService->findLocations($searchQuery);
            if ($searchResults->totalCount > 0) {
                return $this->getResultArray($searchResults->searchHits);
            }
            if ($location->id != $this->configResolver->getParameter( 'content.tree_root.location_id' )) {
                $parentLocation = $this->locationService->loadLocation($location->parentLocationId);
                return $this->getAdditionalContent($parentLocation, $widgetConfiguration);
            }
        }
        return [];
    }

    protected function getResultArray($searchResults, $excludedIds = []) {
        $items = [];

        foreach ($searchResults as $searchResult) {
            if (!in_array($searchResult->valueObject->id, $excludedIds)) {
                $resultLocation = $searchResult->valueObject;
                $resultContent = $this->contentService->loadContent($resultLocation->contentInfo->id);
                $items[] = array(
                    'location' => $resultLocation,
                    'content' => $resultContent
                );
            }
        }

        return $items;
    }

    protected function buildQuery($location, $classIdentifier) {
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
}
