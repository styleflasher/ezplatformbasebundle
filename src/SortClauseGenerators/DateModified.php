<?php

namespace Styleflasher\eZPlatformBaseBundle\SortClauseGenerators;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\DateModified as eZDateModified;

class DateModified extends SortClause
{
    public function generateSortClauses()
    {
        return [
            new eZDateModified($this->sortDirection)
        ];
    }
}
