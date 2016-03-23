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

namespace Styleflasher\eZPlatformBaseBundle\CriterionGenerators;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;

class ChildCriterion extends Criterion
{

    protected $contentTypeIdentifiers = [];

    public function setChildContentTypeIdentifiers(array $contentTypeIdentifiers)
    {
        $this->contentTypeIdentifiers = $contentTypeIdentifiers;
        return $this;
    }

    public function getChildContentTypeIdentifiers()
    {
        return $this->contentTypeIdentifiers;
    }

    public function generateCriterion(Location $location, array $languages = [])
    {
        $criteria = [
            new Visibility(Visibility::VISIBLE),
            new ParentLocationId($location->id),
            new Subtree($location->pathString),
            new LanguageCode($languages)
        ];
        
        if (sizeof($this->contentTypeIdentifiers)) {
            $criteria[] = new ContentTypeIdentifier($this->contentTypeIdentifiers);
        }
        
        return new LogicalAnd($criteria);
    }

}
