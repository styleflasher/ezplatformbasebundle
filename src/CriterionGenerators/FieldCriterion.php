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
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use Styleflasher\eZPlatformBaseBundle\FieldValueProvider;

class FieldCriterion extends Criterion
{

    protected $contentFieldTypeIdentifier = "";
    protected $fieldValueProvider = null;
    protected $operator = Operator::EQ;
    
    public function setOperator($operator) {
        $this->operator = $operator;
    }
    
    public function setContentFieldTypeIdentifier($contentFieldTypeIdentifier)
    {
        $this->contentFieldTypeIdentifier = $contentFieldTypeIdentifier;
        return $this;
    }
    
    public function setFieldValueProvider(FieldValueProvider $fieldValueProvider) {
        $this->fieldValueProvider = $fieldValueProvider;
        return $this;
    }
    
    public function getFieldValueProvider() {
        return $this->fieldValueProvider;
    }

    public function generateCriterion(Location $location, array $languages = [])
    {
        
        $valueProvider = $this->getFieldValueProvider();
        $value = $valueProvider->getValue();
        
        $criteria = [
            new Field($this->contentFieldTypeIdentifier, $this->operator, $value)
        ];
        
        return new LogicalAnd($criteria);
    }

}
