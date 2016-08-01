<?php

namespace Styleflasher\eZPlatformBaseBundle\SortClauseGenerators;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName as eZContentName;

class ContentName extends SortClause
{
    public function generateSortClauses() {
        return [
            new eZContentName($this->sortDirection)
        ];
    }
}

