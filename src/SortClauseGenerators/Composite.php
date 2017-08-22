<?php

namespace Styleflasher\eZPlatformBaseBundle\SortClauseGenerators;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName as eZContentName;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\API\Repository\Values\Content\Location;

class Composite extends SortClause
{
    private $sortClauseGenerators;

    /** @var ConfigResolverInterface */
    protected $configResolver;

    /**
     * @param ConfigResolverInterface $configResolver
     */
    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
        $this->sortClauseGenerators = [];
    }

    public function setSortClauseGenerators($sortClauseGenerators)
    {
        $this->sortClauseGenerators = $sortClauseGenerators;
    }

    public function addSortClauseGenerators($sortClauseGenerators)
    {
        $this->sortClauseGenerators = array_merge($this->sortClauseGenerators, $sortClauseGenerators);
    }

    //typehint
    public function generateSortClauses(Location $location)
    {
        $list = [];
        foreach ($this->sortClauseGenerators as $scg) {
            $sortClauses = $scg->generateSortClauses($location, $this->configResolver->getParameter('languages'));
            $list = array_merge($list, $sortClauses);
        }
        return $list;
    }
}
