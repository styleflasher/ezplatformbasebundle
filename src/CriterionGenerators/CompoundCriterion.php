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
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;

/**
 * CompoundCriterion can hold several simple criteria
 *
 * @author styleflasher GmbH, René Hrdina <rene.hrdina@styleflasher.at>
 */
class CompoundCriterion extends Criterion
{

    protected $criteria = [];
    protected $mode = 'and';
    
    public function setLogicalOrMode() {
        $this->mode = 'or';
    }
    
    public function setLogicalAndMode() {
        $this->mode = 'and';
    }
    
    public function addCriterion(Criterion $criterion) {
        
        $this->criteria[] = $criterion;
    }
    
    public function generateCriterion(Location $location, array $languages = [])
    {
        
        $compoundCriteria = [];
        foreach($this->criteria as $criterion) {
            $compoundCriteria[] = $criterion->generateCriterion($location, $languages);
        }
        
        if ($this->mode === 'or') {
            return new LogicalOr($compoundCriteria);
        }
        else {
            return new LogicalAnd($compoundCriteria);
        }
    }

}
