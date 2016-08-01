<?php

namespace Styleflasher\eZPlatformBaseBundle\SortClauseGenerators;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\DatePublished as eZDatePublished;

class DatePublished extends SortClause
{
    public function generateSortClauses() {
        return [
            new eZDatePublished($this->sortDirection)
        ];
    }
}