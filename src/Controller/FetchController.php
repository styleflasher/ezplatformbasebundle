<?php

/*
 * Copyright (C) 2016 styleflasher GmbH, René Hrdina <rene.hrdina@styleflasher.at>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller as ezPublishCoreController;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController;
use eZ\Publish\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use Pagerfanta\Pagerfanta;
use Styleflasher\eZPlatformBaseBundle\CriterionGenerators\Criterion;
use Styleflasher\eZPlatformBaseBundle\SortClauseGenerators\SortClause;

/**
 * Description of FetchController
 *
 * @author styleflasher GmbH, René Hrdina <rene.hrdina@styleflasher.at>
 */
class FetchController extends ezPublishCoreController
{

    /** @var SearchService */
    protected $searchService;

    /** @var LocationService */
    protected $locationService;

    /** @var ConfigResolverInterface */
    protected $configResolver;

    /** @var ConfigResolverInterfacenfigResolver; */
    protected $criterionGenerator;

    /** @var SortClause; */
    protected $sortClauseGenerator;

    /** @var int */
    protected $topMenuLocationId;

    /** @var int */
    protected $limit;

    /** @var ViewController */
    protected $contentViewController;
    
    /**
     * 
     * @param SearchService $searchService
     * @param LocationService $locationService
     * @param ConfigResolverInterface $configResolver
     * @param Criterion $criterionGenerator
     * @param SortClause $sortClauseGenerator
     */
    public function __construct(ViewController $contentViewController, SearchService $searchService, LocationService $locationService, ConfigResolverInterface $configResolver, Criterion $criterionGenerator, SortClause $sortClauseGenerator)
    {
        $this->contentViewController = $contentViewController;
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->configResolver = $configResolver;
        $this->criterionGenerator = $criterionGenerator;
        $this->sortClauseGenerator = $sortClauseGenerator;
        $this->limit = 10;
    }

    public function setCriterionGenerator(Criterion $criterionGenerator)
    {
        $this->criterionGenerator = $criterionGenerator;
        return $this;
    }
    
    public function setSortClauseGenerator(SortClause $sortClauseGenerator)
    {
        $this->sortClauseGenerator = $sortClauseGenerator;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    public function generateCriterion($location)
    {
        return $this->criterionGenerator->generateCriterion(
            $location,
            $this->configResolver->getParameter('languages')
        );
    }

    public function generateSortClauses($location)
    {
        return $this->sortClauseGenerator->generateSortClauses(
            $location,
            $this->configResolver->getParameter('languages')
        );
    }

    public function getChildNodesAction($locationId, $viewType, $layout = false, array $params = array())
    {
        $location = $this->locationService->loadLocation($locationId);
        
        $query = new LocationQuery();
        $query->filter = $this->generateCriterion($location);
        $query->sortClauses = $this->generateSortClauses($location);

        $request = $this->contentViewController->getRequest();
        $currentPage = $request->query->get('page', 1);

        $children = new Pagerfanta(
            new LocationSearchAdapter( $query, $this->searchService )
        );

        $children->setMaxPerPage( $this->limit );
        $children->setCurrentPage( $currentPage );
        
        return $this->contentViewController->viewLocation(
            $location->id,
            $viewType,
            $layout,
            ['children' => $children] + $params
        );
    }

}
