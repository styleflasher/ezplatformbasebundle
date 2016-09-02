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

namespace Styleflasher\eZPlatformBaseBundle\SortClauseGenerators;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause as APISortClause;

/**
 * Description of BackendValue
 *
 * @author styleflasher GmbH, René Hrdina <rene.hrdina@styleflasher.at>
 */
class BackendValue extends SortClause
{
    /**
     *
     * @var \Styleflasher\eZPlatformBaseBundle\eZ\Publish\Core\Repository\LocationService
     */
    protected $locationService;

    public function generateSortClauses(\eZ\Publish\API\Repository\Values\Content\Location $location) {

        $sortField = $location->sortField;
        $sortOrder = $location->sortOrder;

        return [
            $this->getSortClauseBySortField($sortField, $sortOrder)
        ];

    }

    /**
     * Method copied from eZ\Publish\Core\Repository\LocationService
     *
     * @param int $sortField
     * @param int $sortOrder
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause
     */
    protected function getSortClauseBySortField( $sortField, $sortOrder = APILocation::SORT_ORDER_ASC )
    {
        $sortOrder = $sortOrder == APILocation::SORT_ORDER_DESC ? Query::SORT_DESC : Query::SORT_ASC;
        switch ( $sortField )
        {
            case APILocation::SORT_FIELD_PATH:
                return new APISortClause\Location\Path( $sortOrder );

            case APILocation::SORT_FIELD_PUBLISHED:
                return new APISortClause\DatePublished( $sortOrder );

            case APILocation::SORT_FIELD_MODIFIED:
                return new APISortClause\DateModified( $sortOrder );

            case APILocation::SORT_FIELD_SECTION:
                return new APISortClause\SectionIdentifier( $sortOrder );

            case APILocation::SORT_FIELD_DEPTH:
                return new APISortClause\Location\Depth( $sortOrder );

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_CLASS_IDENTIFIER:

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_CLASS_NAME:

            case APILocation::SORT_FIELD_PRIORITY:
                return new APISortClause\Location\Priority( $sortOrder );

            case APILocation::SORT_FIELD_NAME:
                return new APISortClause\ContentName( $sortOrder );

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_MODIFIED_SUBNODE:

            case APILocation::SORT_FIELD_NODE_ID:
                return new APISortClause\Location\Id( $sortOrder );

            case APILocation::SORT_FIELD_CONTENTOBJECT_ID:
                return new APISortClause\ContentId( $sortOrder );

            default:
                return new APISortClause\Location\Path( $sortOrder );
        }
    }
}
