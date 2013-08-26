<?php

namespace DTA\MetadataBundle\Model\Data;

use DTA\MetadataBundle\Model\Data\om\BasePersonalname;

class Personalname extends BasePersonalname
{
    public function getNameFragments($criteria = NULL, PropelPDO $con = NULL){
        $collection = parent::getNamefragments();
         // Re-sort them by Sequence, numerically
        $collection->uasort(function($a, $b) {
            return $a->getSortableRank() - $b->getSortableRank();
        });
        return $collection;
    }
    
    public function __toString(){
// TODO: If there should be any issue with the order, switch to the more complicated query structure.
//        NamefragmentQuery::create()
//                ->filterByPersonalnameId($this->getId())
//                ->orderByRank('asc')
//                ->find();
        
        $allNF = $this->getNamefragments();
        $result = "";
        foreach($allNF as $nameFragment){
            $result .= $nameFragment->getName() . " ";
        }
        return $result;
    }
 
}