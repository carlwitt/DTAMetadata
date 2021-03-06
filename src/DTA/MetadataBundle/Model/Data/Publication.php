<?php

namespace DTA\MetadataBundle\Model\Data;

use DTA\MetadataBundle\Model\Data\om\BasePublication;
use DTA\MetadataBundle\Model;
use \Propel;
use \PropelCollection;

class Publication extends BasePublication
{
    public static function create($publicationType){
        // check type is valid
        $validTypes = PublicationPeer::getValueSet(PublicationPeer::TYPE);
        if( FALSE === array_search($publicationType, $validTypes)){
            throw new \Exception("Cannot create publication of type $publicationType, use one of ".implode(', ',$validTypes));
        }
        
        $basepublication = new Publication();
        $basepublication->setType($publicationType);
        
        // the type string equals the unqualified class name
        $className = 'DTA\\MetadataBundle\\Model\\Data\\' . $publicationType;
        $specializedPublication = new $className();
        $specializedPublication->setPublication($basepublication);
        return $specializedPublication;
        
    }
    
    /**
     * Retrieves the publication object (volume, chapter, article) which uses this object as core publication.
     */
    public function getSpecialization(){
        // camelcase version of the type e.g. type = VOLUME, becomes Volume
        $publicationType = ucwords(strtolower($this->getType()));
        $getter = 'get'.$publicationType;
        if(method_exists($this, $getter)){
            $result = $this->$getter();
            if($result === null){
                throw new \Exception("Specialisation of Publication with id=".$this->getId().", title=\"".$this->getTitle()."\" and type=\"".$this->getType()."\" is null");
            }
            return $result;
        } else {
            return $this;
        }
    }
    
    /** @return the class name (WITHOUT full qualification) of the specialization class. Might be Publication in case there exists no extra class to represent the publications type. */
    public function getSpecializationClassName(){
        $specializedClassNameParts = explode('\\', get_class($this->getSpecialization())); // fully qualified
        return array_pop($specializedClassNameParts);
    }
    
    /**
     * Used in the select or add control to add works on the fly.
     * @return string
     */
    public function getSelectBoxString(){
        return $this->getShortTitle();
    }
    
    /**
     * Used in displaying all publications (table row view behavior in the data schema definition) to select an author.
     * @return Personalname
     */
    public function getFirstAuthorName(){
        // TODO first person publication might not be the first author
        $personPublications = $this->getPersonPublications();
        if(count($personPublications) == 0 ) return NULL;
        $firstPersonPublication = $personPublications[0];
        $personalNames = $firstPersonPublication->getPerson()->getPersonalnames();
        if(count($personalNames) == 0 ) return NULL;
        return $personalNames[0];
    }
    
    /** Returns a single string combining all title fragments and a volume description. */
    public function getTitleString($withVolumeInformation = true){

        $title = $this->getTitle();
        $result = $title !== NULL ? $title->__toString() : "";
        if($withVolumeInformation && $this->getType() === PublicationPeer::TYPE_VOLUME ){
            $volume = $this->getVolume(); 
            if($volume === NULL){
                throw new \Exception("No volume entity related to volume publication ");//.$this->getId()." ".$this->getShortTitle());
            }
            $result .= $volume->getVolumeSummary();
        }
        
        return $result;
    }
    
    /** Returns all tasks that are closed or open respectively. */
    public function getTasksByClosed($closed){
        
        return Model\Workflow\TaskQuery::create()
                ->filterByPublicationId($this->id)
                ->filterByClosed($closed)
                ->useTasktypeQuery()->orderByTreeLeft()->endUse()
//                ->orderByTasktypeId()
                ->find();
        
    }
    
    /** Returns a short title, suitable for displaying an overview. */
    public function getShortTitle(){
        return $this->getTitlePart();
    }

    public function getTitlePart($part = TitlefragmentPeer::TYPE_SHORT_TITLE, $withVolumeInformation=true){
        $titleFragments = $this->getTitle()->getTitleFragments();
        $result = null;
        // check if the title has the title fragment
        foreach ($titleFragments as $tf ){
            /* @var $tf Titlefragment */
            if($tf->getType() == $part)
                $result = $tf->getName();
        }
        if($result===null){
            $title = $this->getTitle();
            $result = $title !== NULL ? $title->__toString() : "";
        }
        if($withVolumeInformation && $this->getType() === PublicationPeer::TYPE_VOLUME ){
            $volume = $this->getVolume();
            if($volume === NULL) throw new \Exception("No volume entity related to volume publication ".$this->getId()." ".$this->getShortTitle());
            $result .= $volume->getVolumeSummary();
        }
        // no short title available
        return $result;
    }


}
