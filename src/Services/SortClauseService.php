<?php

namespace Styleflasher\eZPlatformBaseBundle\Services;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query;

class SortClauseService
{
    public function __construct() {

    }

    public function generateSortClause( $sortClause, $sortOrder ) {
        $order = ($sortOrder == 0) ? Query::SORT_DESC : Query::SORT_ASC;
        switch ( $sortClause )
        {
            case 1:
                $sortQuery = [new SortClause\Location\Path( $order )];
                break;
            case 2:
                $sortQuery = [new SortClause\DatePublished( $order )];
                break;
            case 3:
                $sortQuery = [new SortClause\DateModified( $order )];
                break;
            case 4:
                $sortQuery = [new SortClause\SectionName( $order )];
                break;
            case 5:
                $sortQuery = [new SortClause\Location\Depth( $order )];
                break;
            case 8:
                $sortQuery = [new SortClause\Location\Priority( $order )];
                break;
            case 9:
                $sortQuery = [new SortClause\ContentName( $order )];
                break;
            default:
                $sortQuery = [new SortClause\Location\Path( $order )];
        }
        return $sortQuery;
    }
}
