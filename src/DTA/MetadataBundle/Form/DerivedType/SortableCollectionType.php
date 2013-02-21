<?php

namespace DTA\MetadataBundle\Form\DerivedType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SortableCollectionType extends \Symfony\Component\Form\Extension\Core\Type\CollectionType {

    /**
     * @var string contains the unqualified model class name to use in the view (for translation, dynamic controls, etc.)
     */
    public $modelClass;
    
    public function getName() {
        return 'sortableCollection';
    }
    
    /**
     * Only Build the prototype with modified placeholders
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        // extract unqualified class name and pass it to the view
        // this allows more accurate control elements (instead of "add component",
        // one can use "add modelClass")
        $dataClassStr = $options['type']->getOption('data_class');
        $parts = explode('\\', $dataClassStr);
        $this->modelClass = array_pop($parts);
        
        $prototypeName = '__' . $this->modelClass . 'ID__';
        
        if ($options['allow_add'] && $options['prototype']) {
            $prototype = $builder->create($prototypeName, $options['type'], array_replace(array(
                'label' => $options['prototype_name'] . 'label__',
            ), $options['options']));
            $builder->setAttribute('prototype', $prototype->getForm());
        }
    }
    
    public function finishView(FormView $view, FormInterface $form, array $options){
        $view->vars['modelClass'] = $this->modelClass;
    }
}
?>