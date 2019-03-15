<?php

namespace Styleflasher\eZPlatformBaseBundle\Services;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;

class MenuService
{
    private $locationService;
    private $searchService;
    private $configResolver;
    private $sortClauseService;

    public function __construct(
        LocationService $locationService,
        SearchService $searchService,
        SortClauseService $sortClauseService,
        $configResolver
    ) {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->configResolver = $configResolver;
        $this->sortClauseService = $sortClauseService;
    }

    public function generateTopmenu( $location ) {
        $rootLocation = $this->locationService->loadLocation($this->configResolver->getParameter( 'content.tree_root.location_id' ));
        $sortArray = $this->sortClauseService->generateSortClause($rootLocation->sortField, $rootLocation->sortOrder);
        $menuConfiguration = $this->getMenuConfiguration();

        $mainMenuItems = $this->fetchChildren(
            $rootLocation,
            $menuConfiguration['main']['classes'],
            $menuConfiguration['main']['excluded_location_ids'],
            $sortArray
        );

        $menuStructure = $this->buildMenuStructure($mainMenuItems, $menuConfiguration, 0);

        $pathArray = explode('/', $location->pathString);
        array_shift($pathArray);
        array_pop($pathArray);

        $menu = [
            'menuStructure' => $menuStructure,
            'has_submenu' => $menuConfiguration['has_submenu'],
            'pathArray' => $pathArray
        ];

        return $menu;
    }

    protected function getMenuConfiguration() {
        $menuConfiguration = [
            'has_submenu' => $this->configResolver->getParameter('menu.has_submenu', 'styleflashere_z_platform_base'),
            'levels' => $this->configResolver->getParameter('menu.levels', 'styleflashere_z_platform_base'),
            'main' => [
                'classes' => $this->configResolver->getParameter('menu.main.classes', 'styleflashere_z_platform_base'),
                'excluded_location_ids' => $this->configResolver->getParameter('menu.main.excluded_location_ids', 'styleflashere_z_platform_base')
            ],
            'sub' => [
                'classes' => $this->configResolver->getParameter('menu.sub.classes', 'styleflashere_z_platform_base'),
                'excluded_location_ids' => $this->configResolver->getParameter('menu.sub.excluded_location_ids', 'styleflashere_z_platform_base')
            ]
        ];

        return $menuConfiguration;
    }

    protected function buildMenuStructure($menuItems, $menuConfiguration, $level) {
        $menuStructure = [];
        foreach ($menuItems->searchHits as $menuItem) {
            $menuStructure[] = [
                'location' => $menuItem->valueObject,
                'submenu' => $this->fetchNextLevelItems($menuItem->valueObject, $menuConfiguration, $level + 1)
            ];
        }

        return $menuStructure;
    }

    protected function fetchNextLevelItems($location, $menuConfiguration, $level) {
        if ( $level > $menuConfiguration['levels'] || !$menuConfiguration['has_submenu'] ) {
            return [];
        }

        $sortArray = $this->sortClauseService->generateSortClause($location->sortField, $location->sortOrder);
        $menuItems = $this->fetchChildren(
            $location,
            $menuConfiguration['sub']['classes'],
            $menuConfiguration['sub']['excluded_location_ids'],
            $sortArray
        );

        return $this->buildMenuStructure($menuItems, $menuConfiguration, $level + 1);
    }

    protected function fetchChildren(
        $subTreeLocation,
        array $typeIdentifiers = [],
        array $excludedLocationIds = [],
        array $sortMethods = []
    ) {
        $criterion = [
            new ContentTypeIdentifier($typeIdentifiers),
            new ParentLocationId([ $subTreeLocation->id ]),
            new Visibility(Visibility::VISIBLE),
        ];

        if (count($excludedLocationIds)) {
            $criterion[] = new LogicalNot(new LocationId($excludedLocationIds));
        }

        $query = new LocationQuery();
        $query->filter = new LogicalAnd($criterion);
        $query->sortClauses = $sortMethods;

        return $this->searchService->findLocations($query);
    }
}
