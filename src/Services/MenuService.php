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

class MenuService
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

    public function generateTopmenu( $location ) {
        $rootLocation = $this->locationService->loadLocation($this->configResolver->getParameter( 'content.tree_root.location_id' ));
        $sortArray = $this->sortClauseService->generateSortClause($rootLocation->sortField, $rootLocation->sortOrder);
        $menuConfiguration = $this->getMenuConfiguration();

        $mainMenuItems = $this->fetchChildren(
            $rootLocation,
            $menuConfiguration['main']['classes'],
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
            'has_submenu' => $this->configResolver->getParameter('menu.has_submenu', 'styleflasher_standard'),
            'levels' => $this->configResolver->getParameter('menu.levels', 'styleflasher_standard'),
            'main' => [
                'classes' => $this->configResolver->getParameter('menu.main.classes', 'styleflasher_standard'),
                'excluded_location_ids' => $this->configResolver->getParameter('menu.main.excluded_location_ids', 'styleflasher_standard')
            ],
            'sub' => [
                'classes' => $this->configResolver->getParameter('menu.sub.classes', 'styleflasher_standard'),
                'excluded_location_ids' => $this->configResolver->getParameter('menu.sub.excluded_location_ids', 'styleflasher_standard')
            ]
        ];

        return $menuConfiguration;
    }

    protected function buildMenuStructure($menuItems, $menuConfiguration, $level) {
        $menuStructure = [];
        $excludeArray = ($level === 0) ? $menuConfiguration['main']['excluded_location_ids'] : $menuConfiguration['sub']['excluded_location_ids'];
        foreach ($menuItems->searchHits as $menuItem) {
            if (!in_array($menuItem->valueObject->id, $excludeArray)) {
                $menuStructure[] = array(
                    'location' => $menuItem->valueObject,
                    'content' => $this->contentService->loadContentByContentInfo($menuItem->valueObject->contentInfo),
                    'submenu' => $this->fetchNextLevelItems($menuItem->valueObject, $menuConfiguration, $level + 1)
                );
            }
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
            $sortArray
        );

        return $this->buildMenuStructure($menuItems, $menuConfiguration, $level + 1);
    }

    protected function fetchChildren(
        $subTreeLocation,
        array $typeIdentifiers = array(),
        array $sortMethods = array()
    ) {
        $criterion = array(
            new ContentTypeIdentifier($typeIdentifiers),
            new ParentLocationId([ $subTreeLocation->id ]),
            new Visibility(Visibility::VISIBLE),
        );

        $query = new LocationQuery();
        $query->filter = new LogicalAnd($criterion);
        $query->sortClauses = $sortMethods;

        return $this->searchService->findLocations($query);
    }
}