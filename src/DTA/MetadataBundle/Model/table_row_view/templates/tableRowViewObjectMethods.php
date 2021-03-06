/**
 * To specify which columns are to be visible in the user display
 * (In the view that lists all database records of a class as a table)
 */
public static function getTableViewColumnNames(){
    $rc = new \ReflectionClass(get_called_class());
    return $rc->getStaticPropertyValue("tableRowViewCaptions");
}

/**
* Calculates the indices of the sortable columns.
*/
public static function getRowViewOrderColumnIndices(){
    $rc = new \ReflectionClass(get_called_class());
    $orderAccessors = $rc->getStaticPropertyValue("tableRowViewOrderAccessors");
    $indices = array();
    $i = 0;
    foreach($rc->getStaticPropertyValue("tableRowViewCaptions") as $caption){
        if(array_key_exists($caption, $orderAccessors)){
            $indices[] = $i;
        }
        $i++;
    }
    return $indices;
}

/**
* Returns the order function name for a given column caption.
*/
public static function getRowViewOrderFunctionName($columnName){
$rc = new \ReflectionClass(get_called_class());
$orderAccessors = $rc->getStaticPropertyValue("tableRowViewOrderAccessors");
if(!array_key_exists($columnName, $orderAccessors))
    return null;
return "orderBy".$orderAccessors[$columnName];
}

/** 
 * To access the data using the specified column names.
 * @param string columnName 
 */
public function getAttributeByTableViewColumName($columnName){
    $rc = new \ReflectionClass(get_called_class());
    $accessor = $rc->getStaticPropertyValue("tableRowViewAccessors")[$columnName];

    // don't use propel standard getters for user defined accessors
    // or for representative selector functions 
    if(!strncmp($accessor, "accessor:", strlen("accessor:"))){
        $accessor = substr($accessor, strlen("accessor:"));
        return call_user_func(array($this, $accessor));
    } else {
        $result = $this->getByName($accessor, \BasePeer::TYPE_PHPNAME);
        if( is_a($result, 'DateTime') )
            $result = $result->format('d/m/Y');
        return $result;
    }
}

/** 
 * @return The propel query object for retrieving the records.
 */
public static function getRowViewQueryObject(){
    $rc = new \ReflectionClass(get_called_class());
    $queryConstructionString = $rc->getStaticPropertyValue("queryConstructionString");
    if($queryConstructionString === NULL){
        $classShortName = $rc->getShortName();
        $package = \DTA\MetadataBundle\Controller\ORMController::getPackageName($rc->getName());
        $queryClass = \DTA\MetadataBundle\Controller\ORMController::relatedClassNames($package, $classShortName)['query'];
        return new $queryClass;
    } else {
        return eval('return '.$queryConstructionString);
    }
}

<?php
    foreach($representativeGetterFunctions as $rgf)
        echo $rgf;
    
    foreach($embeddedGetterFunctions as $egf)
        echo $egf;
    
?>
