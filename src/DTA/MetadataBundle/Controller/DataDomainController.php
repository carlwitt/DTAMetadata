<?php

namespace DTA\MetadataBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use \DTA\MetadataBundle\Model;

/**
 * Route prefix for all action routes, i.e. this page.
 * @Route("/daten")
 */
class DataDomainController extends DTABaseController {

    /** @inheritdoc */
    public static $domainKey = "DataDomain";

    /** @inheritdoc */
    public static $domainMenu = array(
        array("caption" => "Publikationen", "children" => array(
                array('caption' => "Alle Publikationsarten", 'route' => 'home'),
                array('modelClass' => 'Monograph'),
                array('modelClass' => 'Magazine'),
                array('caption' => "Reihen", 'modelClass' => 'Series'),
                array('caption' => "Essays", 'modelClass' => 'Essay'))),
        array("caption" => "Personen", "children" => array(
                array('caption' => "Alle Personen", 'route' => 'home'),
                array('modelClass' => 'Author'),
                array('caption' => "Verleger", 'modelClass' => 'Publisher'),
                array('modelClass' => 'Translator'),
                array('caption' => "Drucker", 'modelClass' => 'Printer'))),
        array("caption" => "Verlage", 'modelClass' => 'PublishingCompany'),
    );

    /**
     * @Route("/", name="dataDomain")
     */
    public function indexAction() {
        // TODO: remove. DataDomain.html.twig is useless now, as the domain menu has been generalized to the top level.
//        $pnq = new Model\PersonalQuery();
        
        return $this->renderControllerSpecificAction('DTAMetadataBundle:DataDomain:index.html.twig', array(
            "person" => ""//$pnq->findOneById(4)->getPersonalnames(),
        ));
    }

    /**
     * New action, embedded into the data domain page.
     * @param type $className
     * @Route("/neu/{className}", name="DataDomainNewRecord")
     */
    public function newAction($className) {
        $form = $this->dynamicForm($className, 0);
        return $this->renderControllerSpecificAction('DTAMetadataBundle::formWrapper.html.twig', array(
            'className' => $className,
            'form' => $form->createView(),
        ));
    }

    /**
     * TODO: finalization. remove test data generator.
     * @Route("/generateTestData", name="generateTestData")
     */
    public function generateTestDataAction() {

        // name fragment types
        
        $vorname = new Model\Namefragmenttype();
        $vorname->setName("Vorname");
        $vorname->save();

        $nachname = new Model\Namefragmenttype();
        $nachname->setName("Nachname");
        $nachname->save();
        
        $adelstitel = new Model\Namefragmenttype();
        $adelstitel->setName("Adelstitel");
        $adelstitel->save();
        
        $generation = new Model\Namefragmenttype();
        $generation->setName("Generation");
        $generation->save();
        
        $pseudonym = new Model\Namefragmenttype();
        $pseudonym->setName("Pseudonym");
        $pseudonym->save();
        
        

        // users
        
        $user = new Model\User();
        $user->setUsername("Frank");
        $user->save();
        $user = new Model\User();
        $user->setUsername("Susanne");
        $user->save();
        $user = new Model\User();
        $user->setUsername("Matthias");
        $user->save();
        $user = new Model\User();
        $user->setUsername("Christian");
        $user->save();
        $user = new Model\User();
        $user->setUsername("Carl");
        $user->save();
        $user = new Model\User();
        $user->setUsername("Alex");
        $user->save();

        // title types (main-, sub- and short title)
        
        $haupttitel = new Model\Titlefragmenttype();
        $haupttitel->setName("Haupttitel");
        $haupttitel->save();
        $untertitel = new Model\Titlefragmenttype();
        $untertitel->setName("Untertitel");
        $untertitel->save();
        $kurztitel = new Model\Titlefragmenttype();
        $kurztitel->setName("Kurztitel");
        $kurztitel->save();

        // Workflow, example task types
        
        $s1 = new Model\Tasktype();
        $s1->setName('Aufgabentypen');
        $s1->makeRoot(); // make this node the root of the tree
        $s1->save();

        $groupA = new Model\Tasktype();
        $groupA->setName('Gruppe A: Double Keying');
        $groupA->insertAsFirstChildOf($s1); // insert the node in the tree
        $groupA->save();
        $s3 = new Model\Tasktype();
        $s3->setName('Textbeschaffung');
        $s3->insertAsFirstChildOf($groupA); // insert the node in the tree
        $s3->save();
        $s4 = new Model\Tasktype();
        $s4->setName('Vorkorrektur');
        $s4->insertAsFirstChildOf($s3); // insert the node in the tree
        $s4->save();
        $s5 = new Model\Tasktype();
        $s5->setName('Zoning');
        $s5->insertAsFirstChildOf($s4); // insert the node in the tree
        $s5->save();

        $groupB = new Model\Tasktype();
        $groupB->setName('Gruppe B');
        $groupB->insertAsNextSiblingOf($groupA);
        $groupB->save();

        $s7 = new Model\Tasktype();
        $s7->setName('GrobiZoning');
        $s7->insertAsFirstChildOf($groupB); // insert the node in the tree
        $s7->save();
        
        // workflow: publication statuses
        
        $unpublished = new Model\Status();
        $unpublished->setName("Unveröffentlicht");
        $unpublished->save();
        
        $published = new Model\Status();
        $published->setName("Veröffentlicht");
        $published->save();
        
//        $rootTask = \DTA\MetadataBundle\Model\TasktypeQuery::create()->findRoot();

        return $this->forward("DTAMetadataBundle:Home:index");
    }

}