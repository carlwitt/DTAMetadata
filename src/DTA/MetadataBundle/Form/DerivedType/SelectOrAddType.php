<?php
/**
 * Adds an add button after the select box to extend the range of existing records if (e.g. add a person while creating a book written by that
 * person without having to open another window and creating the person first, reloading the form and selecting it).
 * 
 * Options:
 *  class               fully qualified class name, e.g. DTA\MetadataBundle\Model\Status
 *  property            attribute to use for getting the string which is displayed in the select box.
 *                      can be a function name that starts with get (since propel will try these automatically),
 *                      but then, the "get"-prefix must be omitted (use 'property' => 'SelectBoxString') to 
 *                      retrieve the caption via the getSelectBoxString function.
 *  searchable          whether to apply the select2 plugin to add typeahead functionality
 */
namespace DTA\MetadataBundle\Form\DerivedType;

use Symfony\Bridge\Propel1\Form\ChoiceList\ModelChoiceList;
use Symfony\Bridge\Propel1\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Using this type in a formbuilder allows selection of an existing entity or creation of a new one. 
 * The gui element (button) for creating a new entity is added via form theming (see dtaFormExtensions.html.twig / selectOrAdd_widget).
 * The javascript is located in selectOrAdd.js.
 * 
 * Usage: specify the class and property e.g.
 *      'class' => 'DTA\MetadataBundle\Model\Place',
 *      'property' => 'Name',
 * The class determines which basic formtype will be used in the selectbox
 * The property determines which attribute will be used to summarize each entity in a select option
 * @author carlwitt
 * 
 * when posting the data, an ajax-friendly response is requested
 * in this case, the additional select box element (<option>) is returned
 * which is then added to the select box before the add button
 * 
 * @todo A switch to control data transfer from the select box to the new entity dialog would be nice.
 * Consider for instance selecting a category. If no match is found using the typeahead, the same data
 * has to be entered into the new entity box again. 
 * The problem is how parse an entity from a string. In case of a person, this is not trivial.
 * But for entities that have only a name attribute, this is feasible.
 */

class SelectOrAddType extends \Symfony\Bridge\Propel1\Form\Type\ModelType {
    
    public function getName() {
        return 'selectOrAdd';
    }
    
    /*
     * Passes the class name of the model to the view and the property that has been used to 
     * display the option of the select element. These two properties are used in the 
     * forms generated by the view.
     * modelClass: which route to chose to request a nested form and to update the database
     * captionProperty: which property to use to generate the newly created option.
     */
    public function finishView(FormView $view, FormInterface $form, array $options){
        
        // fully qualified class name (e.g. DTA\MetadataBundle\Model\Status)
        $className = $options['class'];
        
        // extract the class name (e.g. Status)
        $parts = explode('\\', $className);     // $className is fully classified, e.g. DTA\MetadataBundle\Model\Workflow\Task
        $modelClass = array_pop($parts);        // The class name is the last part (Task)
        $package = array_pop($parts);           // The package name is the second last part (Workflow)
        
        $view->vars['selectOrAddConfiguration'] = array(
            'modalRetrievePathParameters' => array(
                'package'   => $package,
                'className' => $modelClass,
                'property' => $options['property'],
            ),
            'searchable' => $options['searchable'],
            'addButton'  => $options['addButton'],
        );
            
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        
        parent::setDefaultOptions($resolver);
        
        $resolver->setDefaults(array(
            'searchable' => true,          // whether to apply the select2 plugin to add typeahead functionality
            'addButton'  => true,
            'empty_value' => 'Keine Auswahl',
            'empty_data' => null,
            'required'   => false,
        ));
    }
    
        

}

?>
