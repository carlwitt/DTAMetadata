/*
* generated by filterable behavior for "<?php echo $className;?>"
*/
public function sqlFilter($filterString)
{
    // get all methods which start with 'filterBy' and end with 'String'
    $filterMethods = array();
    //ob_start();
    foreach(get_class_methods(new <?php echo $className;?>Query) as $possibleFilterMethod){
        //$methodNameParts = preg_split('/(?=[A-Z])/', $possibleFilterMethod);
        //gives the same, but is slower:
        //if($methodNameParts[0] === 'filter' and $methodNameParts[1] === 'By' and end($methodNameParts) === 'String')
        if(!substr_compare($possibleFilterMethod,'filterBy', 0, strlen('filterBy') ) and !substr_compare($possibleFilterMethod,'String',-strlen('String'),strlen('String'))){
            $functionReflection = new \ReflectionMethod(new <?php echo $className;?>Query, $possibleFilterMethod);
            $parameters = $functionReflection->getParameters();
            //if(count($parameters)==2 ){
                //$rp = new \ReflectionParameter(array(new <?php echo $className;?>Query, $possibleFilterMethod),$($parameters[0]->name));
                //var_dump($rp->getClass()->getName());
                //var_dump($possibleFilterMethod);
                $filterMethods[] = $possibleFilterMethod;
            //}
        }
    }

    //throw new Exception(ob_get_clean());

    $query = $this;
    if(!empty($filterMethods)){
        $firstMethod = array_pop($filterMethods);
        $query = $query->$firstMethod($filterString);
    }else{
        throw new Exception("The class \"".get_class($this)."\" was set as filterable, but there is no filter method defined. Add a filter parameter to the schema file.");
    }
    foreach($filterMethods as $filterMethod){
        $query = $query->_or()->$filterMethod($filterString);
    }
    return $query;
}

<?php
foreach($filterFunctions as $ft)
    echo $ft;
?>