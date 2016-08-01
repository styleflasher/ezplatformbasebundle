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

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field;

/**
 * Description of FieldValue
 *
 * @author styleflasher GmbH, René Hrdina <rene.hrdina@styleflasher.at>
 */
class FieldValue extends SortClause
{
    /** @var string */
    protected $contentTypeIdentifier;
    
    /** @var string */
    protected $fieldIdentifier;
    
    public function __construct($contentTypeIdentifier, $fieldIdentifier, $sortDirection = LocationQuery::SORT_DESC)
    {
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->fieldIdentifier = $fieldIdentifier;
        $this->setSortDirection($sortDirection);
    }
    
    public function generateSortClauses($location, $languages) {
        
        $language = empty($languages) ? $location->contentInfo->mainLanguageCode : $languages[0];
        
        return [
            new Field($this->contentTypeIdentifier, $this->fieldIdentifier, $this->sortDirection, $language)
        ];
    }
    
}
