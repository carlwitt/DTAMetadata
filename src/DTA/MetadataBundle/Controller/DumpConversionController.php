<?php
    
namespace DTA\MetadataBundle\Controller;
use DTA\MetadataBundle\Model;
use DTA\MetadataBundle\Model\Data;
use Exception;
    
/**
 * @author Carl Witt <carl.witt@fu-berlin.de>
 * Convert database dump of the old DTA project database (metadatenbank) by alex siebert
 * into new database schema by carl witt.
 */
class DumpConversionController extends ORMController {
    
    /**
     * Configure
     */
    private $username  = 'root';
    private $password  = 'root'; //garamond4000
    private $database  = 'dtadb';
    //private $dumpPath  = '/Users/macbookdata/Dropbox/DTA/dumpConversion/dtadb_2013-09-29_07-10-01.sql';
    private $dumpPath  = '../temp/dtadb_2013-09-29_07-10-01.sql';
    private $pgDumpPath = '../temp/dtadb_pg';
    //private $mysqlExec = '/Applications/MAMP/Library/bin/mysql'; // for importing the dump
    private $mysqlExec  = 'mysql'; //added "C:\Program Files\MySQL\MySQL Server 5.6\bin" to $PATH
    //private $phpExec   = '/usr/local/php5/bin/php';
    private $phpExec   = 'php';

    private $psql = 'psql';
    /** Stores problematic actions taken in the conversion process. */
    private $messages;
    private $warnings;
    private $errors;
    
    /** Connection used in the target database, can be used to wrap multiple queries in a single transaction for a small speedup. */
    private $propelConnection;
    
    /** For convenience, old Ids are retained whereever possible in the new database. 
     * This requires auto-increment to be off for some id columns and this counter can be used to get a free publication ID, since 
     * publication Ids in the old databse start somewhere from 16000 upwards.
     */
    private $publicationIdCounter = 1;
    
    /**
     * @param type $username MySQL access parameters.
     * @param type $password MySQL access parameters.
     * @param type $database The schema name within the database.
     * @return \PDO
     * @throws Exception
     */
    function connect() {
        $dsn = "mysql:dbname=" . $this->database . ";host=127.0.0.1";
        try {
            return new \PDO($dsn, $this->username, $this->password);
        } catch (\PDOException $e) {
            throw new \Exception("Connection failed: " . $e->getMessage());
        }
    }
        
    /** Converts the legacy database dump into the new format. */
    function convertAction() {
        // during conversion, a lot of memory is allocated
        ini_set('memory_limit', '1200M');
        ini_set('max_execution_time', 1800); //300 seconds = 5 minutes
        //
        // stores warning messages generated during the conversion
        $this->warnings = array();
        $this->messages = array();
        $this->errors   = array();
        
        $this->useDumpConversionFiles();
        
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // ERASE ALL DATA FROM THE WORKING (TARGET DATABASE) vvvvvv
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        
        $this->dropAndSetupTargetDB();
        
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // ERASE ALL DATA FROM THE WORKING (TARGET DATABASE) ^^^^^^^
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        // connect to imported (legacy) database
        $dbh = $this->connect();
            
        // trim and NULL empty strings, remove some old records
        $this->cleanUpOldDatabase($dbh);
        // assert that certain assumptions hold for the dump (unused fields)
        $this->checkOldDatabase($dbh);
        
        $this->propelConnection = \Propel::getConnection(Model\Master\DtaUserPeer::DATABASE_NAME);
        $this->messages[] = array('message' => 'transaction begun on '.Model\Master\DtaUserPeer::DATABASE_NAME);
        
        $this->createTaskTypes();
        
        // names of the functions to wrap in transaction code
        $conversionTasks = array(
            'convertUsers',                 // users first: they are referenced in "last changed by" columns
            'convertPublications',  
            'convertFirstEditions',
            'convertPublicationGroups',
            'convertPartners',
            'convertCopyLocations',
            'convertTasks',                 // tasks are linked to publications and publication groups
            'convertFonts',                 
            'convertPublishingCompanies',
            'convertPlaces',                
            'convertAuthors',               
            'convertSingleFieldPersons',
            'convertSeries',
            'convertMultiVolumes',
            );
        
        foreach ($conversionTasks as $task){
            $this->runTransaction($task, $dbh);
        }
        
        $this->enableAutoIncrement($this->propelConnection);
        $this->useProductionFiles();

        // dump new database
        $dbname = $this->getDatabaseName();
        $dbuser = $this->getDataseUser();
        $dumpfile = $this->pgDumpPath.'_'.date("Y-m-d").'.sql';
        $this->messages[] = array('dump database: ' => $dbname);
        $this->messages[] = array('database user: ' => $dbuser);
        $this->messages[] = array('dumped' => shell_exec("pg_dump -d $dbname -U $dbuser -f $dumpfile"));

        return $this->renderWithDomainData('DTAMetadataBundle:DumpConversion:conversionResult.html.twig', array(
            'warnings' => $this->warnings,
            'messages' => $this->messages,
            'errors'   => $this->errors,
        ));
    }
    
    function runTransaction($task, $dbh){
        $start = microtime(true);
        $this->propelConnection->beginTransaction();
        
        $this->$task($dbh);
        
        $this->propelConnection->commit();
        $time_taken = microtime(true) - $start;
        echo $task." ".$time_taken;
        $this->messages[] = array("finished transaction ".$task=>$time_taken);
    }
    
    /** Use the schema files for dump conversion */
    function useDumpConversionFiles(){
        // current working directory is web
        $dumpConversionSchemasDir = "../src/DTA/MetadataBundle/Resources/schemas_dumpConversion";
        foreach (array('dta_data_schema.xml', 'dta_master_schema.xml', 'dta_workflow_schema.xml', 'dta_classification_schema.xml') as $schema) {
            
            // backup current version in config as production file
            //// WARNING: IF DUMP CONVERTION ABORTS AND IS RESTARTED WITHOUT MANUAL MOVING THE PRODUCTION SCHEMA FILES BACK IN PLACE, THE DUMPCONVERSION SCHEMA FILES ARE CONSIDERED AS PRODUCTION FILES!!!
            //copy("$dumpConversionSchemasDir/../config/$schema", "$dumpConversionSchemasDir/../schemas_final/$schema");

            $this->messages[] = array("bringing dump conversion version of $schema into place", copy("$dumpConversionSchemasDir/$schema","$dumpConversionSchemasDir/../config/$schema"));
        }
        // build propel entity classes
        $this->messages[] = array('building model from dump conversion schemas', shell_exec("$this->phpExec ../app/console propel:model:build"));
    }
    
    /** Use the schema files for dump conversion */
    function useProductionFiles(){
        // current working directory is web
        $productionSchemasDir = "../src/DTA/MetadataBundle/Resources/schemas_final";
        foreach (array('dta_data_schema.xml', 'dta_master_schema.xml', 'dta_workflow_schema.xml', 'dta_classification_schema.xml') as $schema) {
            $this->messages[] = array(
                "bringing dump conversion version of $schema into place",
                //system("cp $productionSchemasDir/$schema $productionSchemasDir/../config/$schema")
                copy("$productionSchemasDir/$schema","$productionSchemasDir/../config/$schema")
            );
        }
        // build propel entity classes
        //$this->messages[] = array('building model from production schemas', system("$this->phpExec ../app/console propel:model:build"));
        $this->messages[] = array('building model from production schemas', shell_exec("$this->phpExec ../app/console propel:model:build"));
    }
    
    function dropAndSetupTargetDB(){

        //recreate source database
        shell_exec("$this->mysqlExec -u $this->username -p$this->password -e \"DROP DATABASE IF EXISTS $this->database\"");
        shell_exec("$this->mysqlExec -u $this->username -p$this->password -e \"CREATE DATABASE $this->database\"");

        // import dump
        $importDumpCommand = "$this->mysqlExec -u $this->username -p$this->password $this->database < $this->dumpPath";
        $this->messages[] = array("import dump command: " => $importDumpCommand);
        //system($importDumpCommand);
        $this->messages[] = array(shell_exec($importDumpCommand));

        //recreate target database
        $dbname = $this->getDatabaseName();
        $dbuser = $this->getDataseUser();
        // WARNING: postgreSQL-specific!
        shell_exec("dropdb -U $dbuser --if-exists $dbname");
        shell_exec("createdb -U $dbuser $dbname");
        // build current database schema
        //$resultBuildDBCode = system("$this->phpExec ../app/console propel:sql:build");
        $resultBuildDBCode = shell_exec("$this->phpExec ../app/console propel:sql:build");
        $this->messages[] = array("building database schema from xml model: " => $resultBuildDBCode );
        
        // import current database schema to target database (ERASES ALL DATA)
        //$resultSetupDB = system("$this->phpExec ../app/console propel:sql:insert --force");
        $resultSetupDB = shell_exec("$this->phpExec ../app/console propel:sql:insert --force");
        $this->messages[] = array("resetting target database: " => $resultSetupDB);
        
        // loads fixtures (task types, name fragment types, etc.)
        //$resultFixturesLoad = system("$this->phpExec ../app/console propel:fixtures:load @DTAMetadataBundle");
        $resultFixturesLoad = shell_exec("$this->phpExec ../app/console propel:fixtures:load @DTAMetadataBundle");
        $this->messages[] = array("loading database fixtures: " => $resultFixturesLoad);
        
    }
    
    
    // parses date string in format 2007-12-11 17:39:30 to \DateTime objects
    function parseSQLDate($dateString){
        
        if($dateString === NULL)
            return NULL;
        
        $dateTime = date_parse($dateString);
        $result = new \DateTime();
        $result->setDate($dateTime['year'], $dateTime['month'], $dateTime['day']);
        $result->setTime($dateTime['hour'], $dateTime['minute'], $dateTime['second']);
        return $result;
    }
    
    function createTaskTypes(){
        
        $taskTypes = array( 
            2 => array(
                'name'=>'Gruppe A: Double Keying',
                'children' => array(
                    array(10=>'Textbeschaffung'),
                    array(58=>'Vorkorrektur'),
                    array(30=>'Zoning'),
                    array(50=>'Abtippen'),
                    array(59=>'Convert2TEIP5'),
                 )
            ),
            3=>array(
                'name'=>'Gruppe B',
                'children' => array(
                    array(31=>'GrobiZoning'),
                    array(45=>'OCR2')
                )
            ),
            4=>array(
                'name' => 'Gruppe C',
                'children' => array(
                    array(20=>'Scannen (fakultativ)'))
            ),
            5=>array(
                'name'=>'Gruppe D (OCR-Workflow, nach Zoning)',
                'children' => array(
                    array(40=>'OCR'),
                    array(55=>'DON'),
                    array(60=>'Compare'),
                    array(65=>'DON2XML'),
                    array(75=>'CoordinateMerge_Don'),
                    array(70=>'Nachkorrektur'))
            )
        );
        
        $root = new Model\Workflow\Tasktype();
        $root->setId(1)
            ->setName('Workflows')
            ->makeRoot()
            ->save($this->propelConnection);
        
        foreach ($taskTypes as $id => $workflow) {
            $taskType = new Model\Workflow\Tasktype();
            $taskType->setId($id)
                    ->setName($workflow['name'])
                    ->insertAsLastChildOf($root)
                    ->save($this->propelConnection);
            
            foreach ($workflow['children'] as $child) {
                $childType = new Model\Workflow\Tasktype();
                $childType->setId(array_keys($child)[0])
                        ->setName($child[array_keys($child)[0]])
                        ->insertAsLastChildOf($taskType)
                        ->save($this->propelConnection);
                
            }
            
        }
        
    }
    
    function convertFonts($dbh){

        $rawData = "SELECT 
                        book.id_book as publication_id
                        ,schriftart as `font_name`
                    FROM
                        metadaten JOIN book ON book.id_book = metadaten.id_book
                    WHERE 
                            length(schriftart) > 0
                    ORDER BY font_name";
        
        $currentFont = new Model\Data\Font();
        $publications = Model\Data\PublicationQuery::create();
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });
            
            if($currentFont->getName() !== $row['font_name']){
                
                $currentFont = new Model\Data\Font();
                $currentFont->setName($row['font_name'])
                            ->save($this->propelConnection);
            }
            
            $publications->findOneById($row['publication_id'])
                         ->addFont($currentFont)
                         ->save();
        }
    }
    
    /**
     * No conversion, just aggregation:
     * Creates a multi-volume parent object for volumes (with the same title and author)
     */
    function convertMultiVolumes(){

        // retrieve all persons
        $persons = Data\Person::getRowViewQueryObject()->find();
        
        foreach($persons as $person){
            /* @var $person \DTA\MetadataBundle\Model\Data\Person */
            // find publications with identical titles ('title' => array(pub1, pub2, ...), ...)
            $publicationsByTitle = array();
            
            $authorsVolumes = Model\Data\VolumeQuery::create()
                ->usePublicationQuery()
                    ->usePersonPublicationQuery()
                        ->filterByRole(Model\Master\PersonPublicationPeer::ROLE_AUTHOR)
                        ->filterByPerson($person)
                    ->endUse()
                ->endUse()
                ->joinWith("Publication")
                ->joinWith("Publication.Title")
                ->joinWith("Title.Titlefragment")
                ->orderByVolumeNumeric()
                ->find();
            
            foreach($authorsVolumes as $volume){
                /* @var $volume \DTA\MetadataBundle\Model\Data\Volume */
                // volumes are identified by identical first author and title 
                // additionally, further authors must be excluded to avoid duplicate creation of multivolumes
                if( 1 === $person->getAuthorIndex($volume->getPublication())){
                    $title = $volume->getPublication()->getTitle()->__toString();
                    $publicationsByTitle[$title][] = $volume;
                }
            }
            
            // aggregate into multivolumes
            foreach($publicationsByTitle as $title => $volumes){
                
//                if( count($volumes) > 1 ){
                    // create multi volume with the given volumes as children
                    $this->createMultiVolume($volumes, $person);
//                } else {
//                    $this->warnings[] = array('volume without siblings'=>$volume->getPublication()->getTitle()->__toString()." id=".$volume->getId());
//                }
            }
            
        }
        
    }
    
    /**
     * Creates a multi volume with the same data as the given volumes.
     * Saves the multi volume and adds the volumes as its children.
     * @param array $volumes Array of type Model\Data\Volume
     * @param Data\Person $person The first author of volumes
     */
    private function createMultiVolume($volumes, $person){
        
        // take the title from the first volume (all titles are assumed to be the same)
        $title = $volumes[0]
            ->getPublication()
            ->getTitle()
            ->copy(true) // deep copy (because of titlefragments)
            ->clearPublications(); // publication was copied, too -> saving causes duplicate publication ID error!
                    
        // create multivolume base publication
        $publicationId = $this->publicationIdCounter++;
        $basePublication = new Data\Publication();
        $basePublication->setId($publicationId)
                ->setType(Data\PublicationPeer::TYPE_MULTIVOLUME)
                ->setTitle($title)
                // other person publications might be volume specific
                ->addPersonPublication(Model\Master\PersonPublication::create($person->getId(), Model\Master\PersonPublicationPeer::ROLE_AUTHOR))
                ->setScopeValue($publicationId)
                ->makeRoot()
                ->save($this->propelConnection);
        
        // create multi volume
        $multiVolume = new Data\MultiVolume();
        $multiVolume->setId($publicationId)
                ->setVolumesTotal(count($volumes))
                ->save($this->propelConnection);
        
        foreach ($volumes as $volume) {
            /* @var $volume Data\Volume */
            $volume->getPublication()
                    ->setScopeValue($publicationId)
                    ->insertAsLastChildOf($basePublication)
                    ->save($this->propelConnection);
        }
        

        
    }
    
    function convertPublications($dbh) {
        
        $rawData = "
            SELECT 
                book.id_book as id

                ,title as `title`
                ,subtitle as `subtitle`
                ,other_title as `subtitle2`
                ,short_title as `shorttitle`
                ,dta_auflage as `printrun`
                ,dta_bibl_angabe as `citation`
                ,FIND_IN_SET(sources.source,'china,don,kt,n/a') as `source_id`
                ,ready as `www_ready`
                
                ,IF(LENGTH(`year`) < 3, NULL, `year`) as `year` -- to sort out a 0 entry
                ,LOCATE('[', `year`) as `year_is_reconstructed`

                ,CASE format
                    WHEN '' THEN NULL 
                    WHEN '4º' THEN '4°' 
                    WHEN '8º' THEN '8°'		-- merge character based differences
                    ELSE format
                END as `format`
    
                ,dta_comments as `dta_comments`
                ,special_comment as encoding_comment
                ,metadaten.planung as `metadata_comment`
                ,dta_comment2 as `edition_comment`
                
                ,dirname as `dirname`
                
                ,genre as `genre`
                ,untergenre as `subgenre`
                ,metadaten.dwds_kategorie1
                ,metadaten.dwds_unterkategorie1
                ,metadaten.dwds_kategorie2
                ,metadaten.dwds_unterkategorie2
                ,type as legacy_type
                ,CASE type
                    WHEN 'M'  THEN 'Book'
                    WHEN 'MS' THEN 'Book'
                    WHEN 'X'     THEN 'Book'
                    WHEN 'MM' THEN 'Volume'
                    WHEN 'MMS' THEN 'Volume'
                    WHEN 'DM' THEN 'Chapter'
                    WHEN 'DS' THEN 'Chapter'
                    WHEN 'JA' THEN 'Article'
                    WHEN 'Reihe' THEN 'Series'
                    WHEN 'Zeitschrift' THEN 'Journal'
                    ELSE type
                END as `publication_type`
                
                ,IF(band_zaehlung = 0, 1, band_zaehlung) as `volume_numeric`    -- single volumes seem to have a zero based index
                ,NULLIF(band_anzahl, 0) as `volumes_total`
                ,band_alphanum as `volume_description`
                ,autor1_lastname    -- to detect multi-volume publications, the author name and title need to match
                
                ,doi as `doi`
                ,umfang as `numpages`
                ,umfang_normiert as `numpages_numeric`
                ,dta_seiten as `pages`
                ,book.log_last_change
                ,user.id_user as `updated_by`
                ,usecase as `usecase`

                ,dta_edition as `edition`
                ,availability                                   -- is 0 only for 16 publications
                ,book.dta_insert_date                           -- is set for for approx. 40 publications
                
                ,fundstellen.id_Fundstellen as `used_copy_location`
                ,NULLIF(startseite,0) as `first_text_page`
                
            FROM book 
                LEFT JOIN metadaten ON book.id_book = metadaten.id_book 
                LEFT JOIN sources   ON book.id_book = sources.id_book
                LEFT JOIN user      ON book.log_last_user = user.id_user
                LEFT JOIN fundstellen ON id_nachweis = id_Fundstellen
            ;";
        
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });
    
            // title ------------------------------------------------------------------------------------
            $title = new Model\Data\Title();
            
            // iterate over title columns and create titlefragments of the according type
            $titleColumns = array(
                "title"=>  Model\Data\TitlefragmentPeer::TYPE_MAIN_TITLE, 
                "subtitle"=>  Model\Data\TitlefragmentPeer::TYPE_SUBTITLE, 
                "subtitle2"=>Model\Data\TitlefragmentPeer::TYPE_SUBTITLE,
                "shorttitle"=>Model\Data\TitlefragmentPeer::TYPE_SHORT_TITLE,);
            
            $titleFragmentIdx = 1;
            foreach($titleColumns as $column=>$type){
                if($row[$column] !== NULL){
                    $fragment = Model\Data\Titlefragment::create($row[$column], $type);
                    $fragment->setSortableRank($titleFragmentIdx);
                    $title->addTitlefragment($fragment);
                    $titleFragmentIdx++;
                }
            }
            if($titleFragmentIdx == 1) $this->errors[] = array('message'=>'Keine Titelangabe gefunden für','book.id_book'=>$row['id']);
            
            // date ------------------------------------------------------------------------------------
            $publishedDate = NULL;
            if($row['year'] != NULL){
                $publishedDate = new Model\Data\Datespecification();
                $reconstructed = false;
                $year = $row["year"];
                if($row['year_is_reconstructed'] == "1"){
                    $reconstructed = true;
                    $year = str_replace("[", "", $year);
                    $year = str_replace("]", "", $year);
                }
                
                $publishedDate->setYear(intval($year, 10))
                              ->setYearIsReconstructed($reconstructed);
            }
            
            // infrequent data ------------------------------------------------------------------------------------
            // put some less frequent data (present only for few publications into the comment field
            $comment  = $row['dta_comments'];
            $comment .= $row['edition'] !== NULL ? "\nEdition: " . $row['edition'] : "";
            $comment .= $row['availability'] == "0" ? "\nGilt als nicht verfügbar." : "";
            $comment .= $row['usecase'] !== NULL ? "\nGrund der Korpuszugehörigkeit: " . $row['usecase'] : "";
            $comment .= $row['metadata_comment'] !== NULL ? "\nKommentar Planung/Metadaten: " . $row['metadata_comment'] : "";
            
            // save ------------------------------------------------------------------------------------
            
            // basic publication data 
            $publication = new Model\Data\Publication();
            $publication->setId($row['id'])
                        ->setCitation($row['citation'])
                        ->setCreatedAt($this->parseSQLDate($row['dta_insert_date']))
                        ->setComment($comment)
                        ->setDatespecificationRelatedByPublicationdateId($publishedDate)
                        ->setDirname($row['dirname'])
                        ->setDoi($row['doi'])
                        ->setEditioncomment($row['edition_comment'])
                        ->setEncodingComment($row['encoding_comment'])
                        ->setFirstpage($row['first_text_page'])
                        ->setFormat($row['format'])
                        ->setLastChangedByUserId($row['updated_by'])
                        ->setLegacyDwdsCategory1($row['dwds_kategorie1'])
                        ->setLegacyDwdsSubcategory1($row['dwds_unterkategorie1'])
                        ->setLegacyDwdsCategory2($row['dwds_kategorie2'])
                        ->setLegacyDwdsSubcategory2($row['dwds_unterkategorie2'])
                        ->setLegacygenre($row['genre'])
                        ->setLegacysubgenre($row['subgenre'])
                        ->setLegacytype($row['legacy_type'])
                        ->setNumpages($row['numpages'])
                        ->setNumpagesnumeric($row['numpages_numeric'])
                        ->setPrintrun($row['printrun'])
                        ->setWwwready($row['www_ready'])
                        ->setSourceId($row['source_id'])
                        ->setTitle($title)
                        ->setType($row['publication_type'])
                        ->setUsedcopylocationId($row['used_copy_location'])
                        ->setUpdatedAt($this->parseSQLDate($row['log_last_change']));

            // for specialized publication types, create the according objects
            switch($row['publication_type']){
                case "Article":
                    $article = new Model\Data\Article();
                    $article->setPublication($publication)
                            ->setPages($row['pages'])
                            ->save($this->propelConnection);
                    break;
                case "Chapter":
                    $chapter = new Model\Data\Chapter();
                    $chapter->setPublication($publication)
                            ->setPages($row['pages'])
                            ->save($this->propelConnection);
                    break;
                case "Volume":
                    $volume = new Model\Data\Volume();
                    $volume->setPublication($publication)
                           ->setVolumeDescription($row['volume_description'])
                           ->setVolumeNumeric($row['volume_numeric'])
                           ->save($this->propelConnection);
                    break;
                case "Book":
                    $volume = new Model\Data\Book();
                    $volume->setPublication($publication)
                           ->save($this->propelConnection);
                    break;
                case "Journal":
                    $volume = new Model\Data\Journal();
                    $volume->setPublication($publication)
                           ->save($this->propelConnection);
                    break;
                // SERIES publications are handled in convertSeries()
                // MULTIVOLUME publication type is handled in convertMultiVolumes()
            }

            $publication->save($this->propelConnection);
            
        }// end for book rows
    }
    
    /** Adds the information about first edition (Erstausgabe) to the publication.  
        Since mostly, only a year, location and a publishing company are needed, the entire information is stored in a plain text format. */
    function convertFirstEditions($dbh){
        
        $rawData = "SELECT 
                        book.id_book as id,
                        first_pub_date,
                        first_pub_name,
                        first_pub_location,
                        first_pub_verlag,
                        first_seiten,
                        first_reihe_titel,
                        first_reihe_band,
                        first_in_title,
                        first_comments,
                        CASE first_status
                            WHEN '0' THEN NULL
                            WHEN '1' THEN _utf8'Erstveröffentlichung'
                            WHEN '2' THEN _utf8'Keine Erstveröffentlichung'
                            WHEN '3' THEN _utf8'Unklar, ob Erstveröffentlichung'
                            ELSE first_status
                        END as `first_status`,
                        first_status_date
                    FROM
                        book
                    WHERE
                        first_pub_date is not null
                            OR first_pub_location is not null
                            OR first_pub_verlag is not null
                            OR first_reihe_titel is not null
                            OR first_seiten is not null
                            OR first_reihe_band is not null
                            OR first_comments is not null
                            OR (first_status is not null and first_status > 0)";
        
        $publications = Model\Data\PublicationQuery::create();
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });

            $fields = array(
                'first_status',
                'first_status_date',
                'first_pub_date',
                'first_pub_name',
                'first_pub_verlag',
                'first_pub_location',
                'first_in_title',
                'first_reihe_titel',
                'first_reihe_band',
                'first_seiten',
                'first_comments');
            $labels = array(
                'Status',
                'Status Erstausgabe, Stand',
                'Erschienen', 
                'Herausgeber',
                'Verlag', 
                'Ort',
                'Erstausgabe in: (Titel)',
                'Titel (R/Z)',
                'Band (R/Z)',
                'Seitenangabe', 
                'Kommentar', 
            );
            
            $firstEditionData = "";
            for ($fieldIdx = 0; $fieldIdx < count($fields); $fieldIdx++) {
                if($row[$fields[$fieldIdx]] !== NULL ){
                    $firstEditionData .= $labels[$fieldIdx] . ": " . $row[$fields[$fieldIdx]] . "\n";
                }
            }
            
            $publications->findOneById($row['id'])
                        ->setFirsteditionComment($firstEditionData)
                        ->save($this->propelConnection);
            
        }
            
    }
    
    function convertSeries($dbh){
        
        $rawData = "SELECT 
                        id_book as publication_id
                        ,dta_reihe_titel as title
                        ,dta_reihe_jahrgang as volume
                        ,dta_reihe_band as issue
                    FROM
                        book
                    WHERE
                        dta_reihe_titel is not null
                    ORDER by dta_reihe_titel,dta_reihe_jahrgang,dta_reihe_band";
        
        $currentSeriesTitle = NULL;
        $currentSeries = NULL;
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });

            // create new series if the series title hasn't been seen before
            if($currentSeriesTitle === NULL || $row['title'] != $currentSeriesTitle){
                
                $title = new Model\Data\Title();
                $title->addTitlefragment(Model\Data\Titlefragment::create($row['title']));
                
                $corePublication = new Model\Data\Publication();
                $corePublication->setId($this->publicationIdCounter++)
                                ->setTitle($title)
                                ->setType(Model\Data\PublicationPeer::TYPE_SERIES);
                
                $currentSeries = new Model\Data\Series();
                $currentSeries->setId($corePublication->getId())
                              ->setPublication($corePublication);
                
                $currentSeriesTitle = $row['title'];
            }
            
            $seriesPublication = new Model\Master\SeriesPublication();
            $seriesPublication->setPublicationId($row['publication_id'])
                              ->setIssue($row['issue'])
                              ->setVolume($row['volume']);
            $currentSeries->getPublication()->getTitleString();
            $currentSeries->addSeriesPublication($seriesPublication)
                          ->save($this->propelConnection);
            
        }
        
    }
    
    function convertPublicationGroups($dbh){
        
        $rawData = "SELECT 
                        book.id_book as publication_id, 
                        groups.id_group as group_id, 
                        group_name,
                        groups.log_last_change as 'last_change'
                    FROM groups, group_books, book
                    WHERE
                            book.id_book = group_books.id_book
                            AND group_books.id_group = groups.id_group
                    ORDER BY groups.id_group";
            
        $lastGroupId = -1;
        $group = NULL;
        $publications = Model\Data\PublicationQuery::create();
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });

            if($row['group_id'] !== $lastGroupId){
                
                $group = new Model\Workflow\Publicationgroup();
                $group->setId($row['group_id'])
                      ->setName($row['group_name'])
                      ->setUpdatedAt($this->parseSQLDate($row['last_change']))
                      ->save($this->propelConnection);
                
                $lastGroupId = $row['group_id'];
            }
            
            $group->addPublication($publications->findOneById($row['publication_id']))
                    ->save($this->propelConnection);
        }
        
    }
    /* ---------------------------------------------------------------------
     * partner
     * ------------------------------------------------------------------ */
    function convertPartners($dbh) {
        
        $rawData = "SELECT 
                        id_book_locations,
                        name,
                        person,
                        mail, web, phone1, adress,
                        NULLIF(log_last_change, '0000-00-00 00:00:00') as `log_last_change`,
                        comments
                    FROM partner";
            
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });
                        
            $partner = new Model\Workflow\Partner();
            $partner->setId($row['id_book_locations'])
                    ->setName($row['name'])
                    ->setContactPerson($row['person'])
                    ->setMail($row['mail'])
                    ->setWeb($row['web'])
                    ->setContactdata('Telefon: ' . $row['phone1'] . "\nAdresse: " . $row['adress'])
                    ->setComments($row['comments'])
                    ->setUpdatedAt($this->parseSQLDate($row['log_last_change']))
                    ->save($this->propelConnection);
        }
    }
        
      // after publications and partners
    function convertCopyLocations($dbh){
        
        $rawData = "SELECT 
                        id_Fundstellen as `copylocation_id`
                        ,book.id_book as `publication_id`
                        ,partner.id_book_locations as `partner_id`
                        ,NULLIF(fundstellen.dta_insert_date, '0000-00-00 00:00:00') as `created_at`
                        ,fundstellen.comments as `comments`
                        ,NULLIF(`accessible`, 2) as `accessible`      -- 2 is currently used for 'not clear'
                        ,fundstellen.log_last_user as `updated_by`
                        ,fundstellen.log_last_change as `updated_at`
                        ,signatur as `catalogue_signature`
                        ,bib_id as `catalogue_internal`
                    FROM
                        fundstellen 
                        LEFT JOIN partner ON 
                            fundstellen.id_book_locations = partner.id_book_locations
                        LEFT JOIN book ON
                            fundstellen.id_book = book.id_book;";
            
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });
                        
            if($row['publication_id'] === NULL){
                $this->errors[] = array(
                    'message' => 'Fundstelle verweist auf nicht-existierende Publikation.',
                    'action' => 'Fundstelle nicht übernommen.'
                );
                continue;
            }
                
            try {

            $copyLocation = new Model\Workflow\CopyLocation();
            $copyLocation->setId($row['copylocation_id'])
                    ->setPublicationId($row['publication_id'])
                    ->setPartnerId($row['partner_id'])
                    ->setCreatedAt($row['created_at'])
                    ->setComments($row['comments'])
                    ->setAvailable($row['accessible'])
                    ->setUpdatedAt($row['updated_at'])
                    ->setCatalogueSignature($row['catalogue_signature'])
                    ->setCatalogueInternal($row['catalogue_internal']);
            $copyLocation->save($this->propelConnection);
            
            } catch (\PropelException $exc) {
                $this->errors[] = array('message' => $exc . 'on inserting copy location');
            }
        }
    }
            
    function convertTasks($dbh){
        
        $rawData = "-- normal tasks (refering to a single publication)
                    SELECT 
                        id_task as `task_id`
                        ,'single' as `type` 
                        ,IF(FIND_IN_SET(task_type,'5,10,20,30,31,40,45,50,55,58,59,60,65,70,75') = 0, null, task_type) as `task_type_id`
                        ,book.id_book as `reference_object`
                        ,NULLIF(open_tasks.id_book_locations,0) as `partner_id`
                        ,NULLIF(id_fundstelle,0) as `copy_location_id`
                        ,NULLIF(open_tasks.id_user,0) as `user_id`
                        ,NULLIF(starttime, '0000-00-00 00:00:00') as `start_date`
                        ,NULLIF(endtime, '0000-00-00 00:00:00') as `end_date`
                        ,open_tasks.comments
                        ,closed
                        ,NULLIF(createDate, '0000-00-00 00:00:00') as `created_at`
                        ,NULLIF(open_tasks.log_last_change, '0000-00-00 00:00:00') as `updated_at`
                    FROM
                        open_tasks 
                            left join book on open_tasks.id_book = book.id_book
                            left join user on open_tasks.id_user = user.id_user
                            left join fundstellen on open_tasks.id_fundstelle = fundstellen.id_Fundstellen
                    WHERE
                            -- ignore tasks that refer to publication groups.
                            -- they are redundantly created, the publication group task contains all the information
                            grouped_task = 0 
                    UNION
                    -- group tasks (referring to all publications of a group)
                    SELECT 
                        id_task as `task_id`
                        ,'group' as `type`
                        ,IF(FIND_IN_SET(task_type,'5,10,20,30,31,40,45,50,55,58,59,60,65,70,75') = 0, null, task_type) as `task_type_id`
                        ,groups.id_group as `reference_object`
                        ,NULLIF(id_book_locations,0)  as `partner_id`
                        ,null as `copy_location_id`
                        ,user.id_user as `user_id`
                        ,NULLIF(starttime, '0000-00-00 00:00:00') as `start_date`
                        ,NULLIF(endtime, '0000-00-00 00:00:00') as `end_date`
                        ,comments
                        ,closed
                        ,NULLIF(createDate, '0000-00-00 00:00:00') as `created_at`
                        ,NULLIF(open_tasks_groups.log_last_change, '0000-00-00 00:00:00') as `updated_at`
                    FROM
                        open_tasks_groups
                            LEFT JOIN user on open_tasks_groups.id_user = user.id_user
                            LEFT JOIN groups ON open_tasks_groups.id_group = groups.id_group";
            
        $publications = Model\Data\PublicationQuery::create();
        $groups = Model\Workflow\PublicationgroupQuery::create();
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });

            if($row['reference_object'] === NULL){
                $this->errors[] = array(
                    'message' => 'Task verweist auf nicht-existente Publikation.',
                    'typ'     => $row['type'],
                    'action'  => "Task $row[task_id] übersprungen",
                    );
                continue;
            }
            
            if($row['task_type_id'] === NULL){
                $this->warnings[] = array(
                    'message' => "Task hat unbekannten Tasktyp.",
                    'action'  => 'Datensatz übersprungen',
                    'task_id in old dump' => $row['task_id']
                    );
                continue;
            }
            
            $task = new Model\Workflow\Task();
            
            $task->setTasktypeId($row['task_type_id'])
                 ->setPartnerId($row['partner_id'])
                 ->setCopylocationId($row['copy_location_id'])
                 ->setResponsibleuserId($row['user_id'])
                 ->setStartDate($this->parseSQLDate($row['start_date']))
                 ->setEndDate($this->parseSQLDate($row['end_date']))
                 ->setComments($row['comments'])
                 ->setClosed($row['closed'])
                 ->setCreatedAt($this->parseSQLDate($row['created_at']))
                 ->setUpdatedAt($this->parseSQLDate($row['updated_at']));

            if($row['type'] === 'single'){
                $publications->findOneById($row['reference_object'])
                        ->addTask($task)
                        ->save($this->propelConnection);
                
            } elseif($row['type'] === 'group'){
                
                $groups->findOneById($row['reference_object'])
                        ->addTask($task)
                        ->save($this->propelConnection);
                
            } else{
                $this->errors[] = array('task refers neither to single publication nor to publication group. legacy task id'=>$row['task_id']);
            }
            
        }
        
    }
        
    /* ---------------------------------------------------------------------
     * publishing company
     * ------------------------------------------------------------------ */
         
    function convertPublishingCompanies($dbh) {
        
        $rawData = "SELECT 
                        id_book as publication_id
                        ,trim(char(9) from trim(publishing_company)) as `publishing_company`
                    FROM
                        (
                            SELECT id_book, SUBSTRING_INDEX( SUBSTRING_INDEX( dta_pub_verlag, ';', 1), ';', -1 ) AS publishing_company FROM book 
                            UNION 
                            SELECT id_book, SUBSTRING_INDEX( SUBSTRING_INDEX( dta_pub_verlag, ';', 2), ';', -1 ) AS publishing_company FROM book 
                            UNION 
                            SELECT id_book, SUBSTRING_INDEX( SUBSTRING_INDEX( first_pub_verlag, ';', 1), ';', -1 ) AS publishing_company FROM book 
                            UNION 
                            SELECT id_book, SUBSTRING_INDEX( SUBSTRING_INDEX( first_pub_verlag, ';', 2), ';', -1 ) AS publishing_company FROM book 
                        ) 
                    as publishingCompanies
                    WHERE publishing_company <> ''
                    order by publishing_company";
         
        $currentPublishingCompany = new Model\Data\Publishingcompany();
        $publications = Model\Data\PublicationQuery::create();
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });
                        
            if($row['publishing_company'] !== $currentPublishingCompany->getName()){
                
                $currentPublishingCompany = new Model\Data\Publishingcompany();
                $currentPublishingCompany->setName($row['publishing_company'])
                                         ->save($this->propelConnection);
            }
            
            $publication = $publications->findOneById($row['publication_id']);
            $publication->setPublishingcompany($currentPublishingCompany)
                        ->save($this->propelConnection);
        }
    }
    
    /* ---------------------------------------------------------------------
     * place
     * ------------------------------------------------------------------ */
         
    function convertPlaces($dbh) {
        
        $rawData = "
                SELECT 
                    id_book as publication_id
                    ,CASE location
                        WHEN 'Frankfurt a. M' THEN 'Frankfurt (Main)'
                        WHEN 'Freiburg i. Br.' THEN 'Freiburg (Breisgau)'
                        WHEN 'Halle a. S.' THEN 'Halle (Saale)'
                        WHEN 'Leipzig (fingierte Druckorte)' THEN 'Leipzig'
                        ELSE trim(CHAR(9) FROM trim(location))
                    END as `location`
                FROM
                    -- split und union semicolon-separated places
                    (SELECT 
                        id_book
                        ,SUBSTRING_INDEX(SUBSTRING_INDEX(location, ';', 1), ';', - 1) as location
                    FROM
                        (SELECT id_book, dta_pub_location AS location FROM book 
                         UNION SELECT id_book, first_pub_location AS location FROM book) as places
                        
                    UNION SELECT
                        id_book
                        ,SUBSTRING_INDEX(SUBSTRING_INDEX(location, ';', 2), ';', - 1) as location
                    FROM
                        (SELECT id_book, dta_pub_location AS location FROM book 
                         UNION SELECT id_book, first_pub_location AS location FROM book) as places
                        
                    UNION SELECT 
                        id_book
                        ,SUBSTRING_INDEX(SUBSTRING_INDEX(location, ';', 3), ';', - 1) as location
                    FROM
                        (SELECT id_book, dta_pub_location AS location FROM book 
                         UNION SELECT id_book, first_pub_location AS location FROM book) as places
                    ) as places
                WHERE
                    location IS NOT NULL
                    AND LENGTH(location) > 0
                ORDER BY 
                    location";
           
        $currentPlace = new Model\Data\Place();
        $publications = Model\Data\PublicationQuery::create();
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });
                
            if($currentPlace->getName() !== $row['location']){
                
                $currentPlace = new Model\Data\Place();
                $currentPlace->setName($row['location'])
                        ->save($this->propelConnection);
                            
            }
                
             $publication = $publications->findOneById($row['publication_id']);
             $publication->setPlace($currentPlace)
                         ->save($this->propelConnection);
        }
    }
    
    /* ---------------------------------------------------------------------
     * user
     * ------------------------------------------------------------------ */
         
    function convertUsers($dbh) {
        
        $rawData = "SELECT * FROM user";
        
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });
                        
            $user = new Model\Master\DtaUser();
                
            // password encryption
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $user->setSalt(md5(rand(-1239432, 23429304)));
            $saltedPassword = $encoder->encodePassword('$dta010', $user->getSalt());
                
            $user->setId($row['id_user'])
                    ->setUsername($row['name'])
                    ->setMail($row['mail'])
                    ->setPassword($saltedPassword)
                    ->setCreatedAt($this->parseSQLDate($row['creation_date']))
                    ->save($this->propelConnection);
        }
        
    }
        
    /* ---------------------------------------------------------------------
     * author
     * ------------------------------------------------------------------ */
         
    function convertAuthors($dbh) {
        
        // merge autor1_xxx and autor2_xxx columns into one table
        // autor3_xxx is never used.
        $rawData = "SELECT  
                      id_book
                      ,firstname 
                      ,lastname 
                      ,spelling
                      ,pnd
                    FROM (
                        SELECT 
                            id_book
                            ,autor1_prename as firstname 
                            ,autor1_lastname as lastname
                            ,autor1_spelling as spelling
                            ,autor1_pnd as pnd 
                        FROM book
                        WHERE autor1_prename <> '' OR autor1_lastname <> '' OR autor1_pnd <> ''
                        UNION 
                        SELECT 
                            id_book
                            ,autor2_prename as firstname
                            ,autor2_lastname as lastname
                            ,NULL as spelling
                            ,autor2_pnd as pnd 
                        FROM book
                        WHERE autor2_prename <> '' OR autor2_lastname <> '' OR autor2_pnd <> ''
                    ) as names 
                    ORDER BY
                        lastname, 
                        firstname, 
                        pnd DESC -- NULL pnds come second and the record with a pnd is used as base for the merge of subsequent persons with same name";
                      
        
        $currentPerson = NULL;
        
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });

            // create new person if the next row can not be considered duplicate of the current person
            if( NULL === $currentPerson || FALSE === $currentPerson->match($row) ){
                
                $gndCollision = NULL;
                // try to detect duplicates with duplicate gnds (duplicates apart from $currentPerson)
                if($row['pnd'] !== NULL ) {
                    $gndCollision = Model\Data\PersonQuery::create()->findOneByGnd($row['pnd']);
                }
                if($gndCollision !== NULL){
                    $currentPerson = $gndCollision;
                } else{

                    // create the name object
                    $name = new Model\Data\Personalname();
                    if ($row['firstname'] !== NULL)
                        $name->addNamefragment(Model\Data\Namefragment::create($row['firstname'], Model\Data\NamefragmentPeer::TYPE_FIRST_NAME));
                    if ($row['lastname'] !== NULL)
                        $name->addNamefragment(Model\Data\Namefragment::create($row['lastname'], Model\Data\NamefragmentPeer::TYPE_LAST_NAME));
                    if ($row['spelling'] !== NULL)
                        $name->addNamefragment(Model\Data\Namefragment::create($row['spelling'], Model\Data\NamefragmentPeer::TYPE_SPELLING));

                    $currentPerson = new Model\Data\Person();
                    $currentPerson->setGnd($row['pnd'])            // does nothing if pnd is NULL
                            ->addPersonalname($name)
                            ->save($this->propelConnection);

                }
                
            }
            
            $publication = Model\Data\PublicationQuery::create()->findOneById($row['id_book']);
            $publication
                    ->addPersonPublication(Model\Master\PersonPublication::create($currentPerson->getId(), 'AUTHOR'))
                    ->save($this->propelConnection);
        }
    }
        
    /* ---------------------------------------------------------------------
     * translator, publisher, author (with the entire information stored in a single string)
     * some columns also contain more than one person, separated by semicolons
     * ------------------------------------------------------------------ */
         
    function convertSingleFieldPersons($dbh) {
        
        // there are a few persons which don't have first name/last name columns, so the names must be split
        $rawData = "
            SELECT 
                id_book as publication_id            
                ,trim(char(9) from trim(person)) as person
                ,role
                ,LOCATE(',', person) as comma_position
                ,LOCATE(' ', person) as space_position
                ,NULLIF(substring(substring(person FROM LOCATE('#', person)) from 2), '') as gnd
            FROM (
                
            -- first persons (if separated by ';')
                SELECT DISTINCT 
                    id_book, SUBSTRING_INDEX( SUBSTRING_INDEX( person, ';', 1), ';', -1 ) as person, role
                FROM (
                    SELECT id_book, uebersetzer AS person, 'TRANSLATOR' as role FROM book
                    UNION
                    SELECT id_book, publisher AS person, 'PUBLISHER' as role FROM book
                    UNION
                    SELECT id_book, dta_in_autor AS person, 'AUTHOR' as role FROM book
                    UNION
                    SELECT id_book, autor1_syn_names AS person, 'SYNONYM' as role FROM book
                ) as condensedNames 
                WHERE person IS NOT NULL AND LENGTH(person) > 2 	-- for some reason, strings of length 2 survive the trim operation
                    
                UNION
                    
            -- second persons (if separated by ';')
                SELECT DISTINCT 
                        id_book, SUBSTRING_INDEX( SUBSTRING_INDEX( person, ';', 2), ';', -1 ) as person, role
                FROM (
                    SELECT id_book, uebersetzer AS person, 'TRANSLATOR' as role FROM book
                    UNION
                    SELECT id_book, publisher AS person, 'PUBLISHER' as role FROM book
                    UNION
                    SELECT id_book, dta_in_autor AS person, 'AUTHOR' as role FROM book
                    UNION
                    SELECT id_book, autor1_syn_names AS person, 'SYNONYM' as role FROM book
                ) as condensedNames 
                WHERE person IS NOT NULL AND LENGTH(person) > 2
            ) as names
            ORDER BY person";
                
        $publications = Model\Data\PublicationQuery::create();
        $currentPerson = new Model\Data\Person();
        $currentPersonIdentifier = NULL;
        $synonymAdded = false;
        
        foreach ($dbh->query($rawData)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // encode all data from the old database as UTF8
            array_walk($row, function(&$value) { $value = $value === NULL ? NULL : utf8_encode($value); });
                
            // check if a new person identifier is found
            if($row['person'] !== $currentPersonIdentifier){
                
                // if a GND is given, check whether the person already exists by looking up the GND
                $collision = Model\Data\PersonQuery::create()->findOneByGnd($row['gnd']);
                if($row['gnd'] !== NULL && $collision !== NULL){
                    $currentPerson = $collision;
                // no duplicate, create new
                } else {
                    $currentPerson = Model\Data\Person::createFromArray($row);
                }
             
                $currentPersonIdentifier = $row['person'];
                $synonymAdded = false;
            }
            
            
            // person wurde im Feld Synonymer Name Autor 1 angegeben 
            if($row['role'] === "SYNONYM" && ! $synonymAdded ){
                
                $author = $publications
                        ->findOneById($row['publication_id'])
                        ->getFirstAuthorName()
                        ->getPerson();
                $author
                        ->addPersonalname($currentPerson->getPersonalnames()->getFirst())
                        ->save($this->propelConnection);
                
            // person wurde als Autor der Serie, Verleger oder Übersetzer angegeben
            } else {
                
                $personPublication = new Model\Master\PersonPublication();
                $personPublication->setPerson($currentPerson)
                        ->setRole($row['role']);

                // falls es sich um den Autor einer Serie handelt, bitte per Hand Daten übernehmen
                if($row['role'] === "AUTHOR"){
                    $this->errors[] = array('Bitte von Hand konvertieren'=>$row['person'], 'autor in: '=>$row['publication_id']);
                } else {
                    $publications->findOneById($row['publication_id'])
                            ->addPersonPublication($personPublication)
                            ->save($this->propelConnection);
                }
                
            }
            
            
        }
    }
        
    function checkOldDatabase(\PDO $dbh) {
        

        // normally, book.year and book.dta_pub_date contain the same values in all rows.
        // check if that is still the case with the current input dump
        $checkYearPubDateSame = "select id_book as book_id, `year`, `dta_pub_date` from book where `year` <> `dta_pub_date`";

        foreach ($dbh->query($checkYearPubDateSame)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            
            $this->warnings[] = array(
                'year and dta_pub_date differ (only year will be converted to new database)'=>
                $row['book_id'] . " year: $row[year] dta_pub_date: $row[dta_pub_date]"
            );
        }
        
        // the "seiten" field refers to excerpts and shouldn't be used on monographs and multivolumes 
        $checkQuery = " SELECT 
                            book.id_book, title, autor1_lastname, year, dta_seiten, type
                        FROM
                            book join metadaten on book.id_book = metadaten.id_book
                        WHERE
                            dta_seiten is not null 
                            AND type in ('M','MM','DM')";
        
        foreach ($dbh->query($checkQuery)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            
            $this->warnings[] = array(
                'seitenangabe auf publikationstyp, der dies nicht unterstützt'=>"",
                'id_book' => $row['id_book'],
                'title' => $row['title'],
                'autor' => $row['autor1_lastname'],
                'dta_seiten' => $row['dta_seiten']
            );
        }
        
        // fields that contain no data or outdated data. They are not covered by the dump conversion routines.
        $unusedFields = array(
            'book' => array(
                'autor3_spelling', 'autor2_syn_names', 'autor3_prename', 'autor3_lastname', 'autor3_spelling', 'autor3_syn_names', 'autor3_pnd', 
                'dta_quelle', 'dta_pub_name', 
                'zs_nummer', 'zs_jahrgang', 'zs_hg', 'zs_titel', 
                'type_pub', 
                'erschienen_in', 'dta_uebersetzer', 
                'first_in_autor', 'first_auflagenvermerk', 'first_publisher', 'first_verlag', 'first_reihe_jahrgang', 'first_bibl_angabe'),
            'metadaten' => array(
                'book_sides', 'dta_book_sides', 
                'log_last_change', 'log_last_user',
                'klassifikation',
                'prioritaet',
                'encoding_desc',
                'type_first'
            ),
            'open_tasks_groups' => array(
                'task_name',
                'realendtime',
                'comments',
                'physical_location',
                'parent_task',
                'active', 'activate_date',
            ),
            'open_tasks' => array(
                'task_name',
                'realendtime',
                'physical_location',
                'parent_task',
                'active', 'activate_date',
            ),
            'partner' => array(
                'adress',
                'phone1', 'phone2', 'phone3', 'fax',
                'log_last_change', 'log_last_user'
            ),
            'fundstellen' => array(
                'quality', 'source'
            ),
            'user' => array(
                'phone', 'pw', 'creation_date', 'last_use', 'last_book_id',
            ),
            'groups' => array(
                'log_last_change', 'log_last_user'
            )
        );
        
        foreach ($unusedFields as $table => $fields){
            
            foreach($fields as $field){
                
                $query = "SELECT `$field` FROM `$table` WHERE `$field` IS NOT NULL GROUP BY `$field`;";
                foreach ($dbh->query($query)->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $this->warnings[] = array(
                        'some values in ignored column are not null'=> "$table.$field",
                    );
                    break;
                 }
                
            }
            
        }
        
    }
    function cleanUpOldDatabase(\PDO $dbh) {
        
        // remove unused tables
        $queries[] = "DROP table `dtadb`.`corpus_use`;
                      DROP table `dtadb`.`lastusergroups`;";
        
        
        
        
        // trim all text columns and set empty strings to NULL
        foreach ($dbh->query("SHOW tables") as $row) {
            
            $relation = $row["Tables_in_" . $this->database];
            
            $getTextColumns = "SHOW COLUMNS FROM $relation WHERE 
                                `Type` LIKE 'varchar%' -- varchars of any length 
                                OR `Type` LIKE '%text' -- text and mediumtext";
            
            $trimCommands = array();
            
            foreach ($dbh->query($getTextColumns) as $col) {
                
//                $fields[$relation][] = array($col["Field"], $col["Type"]);
                $trimCommands[] = "$col[Field] = NULLIF(trim(CHAR(9) FROM trim($col[Field])),'')";
            }
            
            if(count($trimCommands) > 0){
                $updateQuery = "UPDATE $relation SET " . implode(",", $trimCommands);
                $pdoStatement = $dbh->query($updateQuery);
                $affectedRows = $pdoStatement !== false ? $pdoStatement->rowCount() : $dbh->errorInfo();

                $this->messages[] = array(
                    'message' => "All columns of table $relation trimmed. Empty strings are set to NULL.", 
                    'affected rows' => $affectedRows,
                    'query'=>$updateQuery);
            }
        }
        
        // !!! remove an old test record
            
        $queries[] = "DELETE FROM `dtadb`.`book` WHERE `id_book`='17251';
                      DELETE FROM `dtadb`.`metadaten` WHERE `id_book`='17251';";
                          
        // !!!
            
        foreach ($queries as $query) {
            $this->messages[] = array("clean up database command: " => $query);
            $dbh->query($query);
        }
    }
        
    function nearDuplicateProposalAction() {
        
        $dbh = $this->connect();
            
        $personsQ = "
            SELECT DISTINCT * FROM
                (SELECT CONCAT( TRIM(autor1_prename), ' ', TRIM(autor1_lastname)) FROM book 
                UNION
                 SELECT CONCAT( TRIM(autor2_prename), ' ', TRIM(autor2_lastname)) FROM book )
                as pcs";
                    
        $array = $dbh->query($personsQ)->fetchAll(\PDO::FETCH_COLUMN, 0);
//        print_r($array);
        // search split 
            
        $candidates = array();
        for ($i = 0; $i < count($array); $i++) {
            for ($j = $i; $j < count($array); $j++) {
                $similarity = -1;
                similar_text($array[$i], $array[$j], $similarity);
                if ($similarity > 70 && $array[$i] != $array[$j]) {
                    echo $array[$i] . "<br/> " . $array[$j] . "<br/><br/>";
                    $candidates[] = array($array[$i], $array[$j]);
                }
            }
        }
            
            
//        foreach ($candidates as &$pair) {
//            echo $pair[0] . "<br/>";
//            echo $pair[1] . "<br/><br/>";
//            
//            $books = 
//            "Select 
//                title, `year`, dta_pub_verlag, autor1_prename, autor1_lastname
//            from
//                book
//            where
//                dta_pub_verlag in (\"$pair[0]\", \"$pair[1]\")
//            order by `dta_pub_verlag`";
//            
//            $pair['books'] = $dbh->query($books)->fetchAll();
//        }
//        
//        return $this->render("DTAMetadataBundle:DumpConversion:pcDuplicates.csv.twig", array('candidates' => $candidates));
    }
        
    function nearDuplicateProposalAction_PublicationCompany() {
        $array = array("Ackermann", "Agentur des Rauhen Hauses", "Akademie der Wissenschaften", "Akademische Buchhandlung", "Alberti", "Allgemeinen Sport-Zeitung", "Amelang", "Andreä", "Arnold", "Aschendorff", "Asher & Co.", "Barth", "Barthol", "Bassermann", "Bauer", "Baumgärtner", "Beck", "Bibliographisches Institut", "Bildungs-Gesellschaft für Arbeiter", "Blätter für die Kunst", "Bohn", "Bonz", "Bornträger", "Braumüller", "Braun", "Braun und Schneider", "Breitkopf & Härtel", "Klemm", "Breitkopf und Härtel", "Brockhaus", "Brockhaus & Avenarius, Schrag", "Brunet", "Buchhandlung der Erziehungsanstalt", "Buchhandlung des Waisenhauses", "Bädeker", "Böhlau", "Cassirer", "Christmann", "Cohen", "Conrad", "Coppenrath", "Cosmos", "Costenoble", "Cotta", "Cotta; Kröner", "Craz", "Creutz", "Curt", "Decker", "Deichert", "Diederichs", "Dienemann", "Dieterich", "Dieterichsche Buchhandlung", "Dietz", "Dorn", "Duncker", "Duncker & Humblot", "Duncker und Humblot", "Dyk", "Dümmler", "Elischer", "Engelhardt", "Engelhorn", "Engelmann", "Enke", "Entsch", "Ernst", "Ernst & Korn", "Ettinger", "Fehsenfeld", "Felix", "Fischer", "Flammer und Hoffmann", "Fleischer", "Fontane", "Fontane & Co.", "Franckh", "Fricke", "Friedrich", "Fritzsch", "Frommann", "Frölich", "Fues", "Gaertner", "Gerold", "Gerstenberg", "Giel", "Giesecke & Devrient", "Goebhardt", "Groos", "Grote", "Groß", "Grädener & Richter", "Gräff", "Gräffer", "Guilhauman", "Guttentag", "Göschen", "Haack", "Habel", "Haering", "Haessel", "Hahn", "Hallberger", "Hammerich", "Hartknoch", "Hartleben", "Haude und Spener", "Heckenast", "Heckenast; Wigand", "Heinrichshofen", "Helwing", "Henne", "Hermann", "Hertz", "Hesse", "Heyer", "Heyne", "Hilscher", "Himburg", "Hirschfeld", "Hirschwald", "Hirzel", "Hitzig", "Hoff", "Hoffmann", "Hoffmann und Campe", "Hofmann", "Hrsg. d. Deutschen Zeitung; Göschen", "Huth", "Hölder", "Hölscher", "in Commission in der J. C. Hermannschen Buchhandlung", "Industrie-Comptoir", "Insel", "Issleib", "Janke", "Jent & Gaßmann", "Junius", "Kayser", "Keil", "Klostermann", "Knapp", "Korn", "Krabbe", "Kreidel", "Kriele", "Kummer", "Künast", "Lagarde und Friederich", "Landgraf", "Langen", "Laupp", "Lentner", "Levysohn", "Lewy", "Liesching", "Literarische Anstalt", "Literarisches Comptoir", "Literarisches Institut", "Loewes", "Logier", "Lorck", "Lucas", "Lüderitz", "Lüstenöder", "Löwenthal", "Macklot", "Mallinckrodt", "Matzdorff", "Maurer", "Max", "Mayer", "Meidinger", "Meissner", "Meißner", "Mendelssohn", "Meyer", "Michaelis und Bispink", "Mittler", "Mohr", "Mohr u: Zimmer", "Mohr und Zimmer", "Mühlmann", "Mylius", "Mäcken", "Naturforschender Verein", "Nauck", "Naumann", "Neff", "Nicolai", "Nicolovius", "Niemeyer", "Normalschul-Buchdruckerey", "Oldenbourg", "Oppenheim", "Orell", "Orell, Gessner, Füssli", "Osiander", "Paetel", "Palm", "Parey", "Perrenon", "Perthes", "Perthes und Besser", "Pfeffer", "Rackhorst", "Realschulbuchhandlung", "Reimarus", "Reimer", "Rein", "Reissner", "Richter", "Riemann", "Rosner", "Rücker", "Rümpler; Grimpe", "Röwer", "Sander", "Sassenbach", "Sauerländer", "Schade", "Schaub", "Schickhardt & Ebner", "Schmeitzner", "Schmerber", "Schmidt", "Schmorl & von Seefeld", "Schott", "Schrag", "Schroedel & Simon", "Schroeder", "Schul-Buchandlung", "Schulbuchhandlung", "Schultze", "Schuster & Loeffler", "Schwan", "Schweighauser", "Schweizerbart", "Schwers", "Schwetschke", "Seemann", "Selbstverlag", "Seydel", "Siemens", "Sollinger", "Spemann", "Springer", "Spurny", "Stage", "Stahel", "Steffens", "Strauß", "Tauchnitz", "Tempsky", "Teubner", "Toeche; Pormetter", "Trewendt", "Trübner", "Unger", "Unzer", "Urania", "Vaillant-Carmanne", "Vandenhoeck", "Vandenhoeck und Ruprecht", "Vandenhoek und Ruprecht", "Veit", "Veit & Co.", "Velhagen & Klasing", "Velhagen und Klasing", "Vereins-Buchhandlung", "Vereinsbuchhandlung", "Verlag der Instituts-Buchh. und in Commission bei Crusius ", "Verlag der Volksbuchhandlung", "Verlag des Bibliographischen Bureaus", "Verlags-Magazin", "Vieweg", "Vogel", "Voigt", "Voss", "Voß", "Wagner", "Wallishauser", "Wallishauser", "Wallishausser", "Walther", "Weber", "Weidmann", "Weidmann Erben und Reich", "Weidmanns Erben und Reich", "Weigel und Schneider", "Weise", "Weiß", "Wiegand, Hempel & Parsey", "Wiegandt, Hempel & Parsey", "Wiener Verlag", "Wigand", "Wilmans", "Winter", "Metzler");
            
        $candidates = array();
        for ($i = 0; $i < count($array); $i++) {
            for ($j = $i + 1; $j < count($array); $j++) {
                $similarity = -1;
                similar_text($array[$i], $array[$j], $similarity);
                if ($similarity > 80 && $array[$i] != $array[$j]) {
//                    echo $array[$i] . "<br/> " . $array[$j] . "<br/><br/>";
                    $candidates[] = array($array[$i], $array[$j]);
                }
            }
        }
            
        // find books for candidates
        $dbh = $this->connect();
        foreach ($candidates as &$pair) {
            echo $pair[0] . "<br/>";
            echo $pair[1] . "<br/><br/>";
                
            $books =
                    "Select 
                title, `year`, dta_pub_verlag, autor1_prename, autor1_lastname
            from
                book
            where
                dta_pub_verlag in (\"$pair[0]\", \"$pair[1]\")
            order by `dta_pub_verlag`";
                
            $pair['books'] = $dbh->query($books)->fetchAll();
        }
            
        return $this->render("DTAMetadataBundle:DumpConversion:pcDuplicates.csv.twig", array('candidates' => $candidates));
    }

    /** For conversion, some id columns are created as non-auto incrementing. To be able to work with the database conveniently, auto-incrementing is enabled manually. 
     * This is postgres specific logic. Maybe that's also the reason why a migration won't do (propel doesn't even seem to recognize the difference between the schemas if auto-increment is on/off).
     * Note that changing the schema is necessary, and the propel classes need to be rebuild afterwards. 
     * php /Users/macbookdata/NetBeansProjects/DTAMetadata/app/console propel:model:build
     */
    public function enableAutoIncrement($propelConnection) {
        
        // publication.id
        $enableAutoIncrement = function($table, $column, $propelConnection){
            $sequenceName = implode("_", array($table, $column, 'seq'));
            $create =
            "CREATE SEQUENCE $sequenceName INCREMENT 1 NO CYCLE;                            -- create sequence";
            $default =
            "ALTER TABLE $table ALTER $column SET DEFAULT nextval('$sequenceName');         -- add default value for id column";
            $own =
            "ALTER SEQUENCE $sequenceName OWNED BY $table.$column;                          -- declare dependency of sequence to column (if moved or deleted)";
            $setMin =
            "SELECT setval( '$sequenceName', max($column)+1 ) FROM $table;                  -- make sequence start after highest value in publications";
            
            $stmt = $propelConnection->prepare($create);    $stmt->execute();
            $stmt = $propelConnection->prepare($default);   $stmt->execute();
            $stmt = $propelConnection->prepare($own);       $stmt->execute();
            $stmt = $propelConnection->prepare($setMin);    $stmt->execute();
        };
        
        // data
        $enableAutoIncrement('publication', 'id', $propelConnection);
        
        // master
        $enableAutoIncrement('dta_user', 'id', $propelConnection);
        
        // workflow
        $enableAutoIncrement('copy_location', 'id', $propelConnection);
        $enableAutoIncrement('tasktype', 'id', $propelConnection);
        $enableAutoIncrement('partner', 'id', $propelConnection);
        $enableAutoIncrement('publicationgroup', 'id', $propelConnection);
        
    }

    //DEBUG
    //public $propelConnection;
    /*public function addUserAction($name, $password){
        echo "addUser ".$name;
        //$this->propelConnection = \Propel::getConnection(Model\Master\DtaUserPeer::DATABASE_NAME);
        $this->get('logger')->log('error','ADD USER '.$name);
        $this->propelConnection = \Propel::getConnection(Model\Master\DtaUserPeer::DATABASE_NAME);
        $this->propelConnection->beginTransaction();
        $user = new Model\Master\DtaUser();

        // password encryption
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $user->setSalt(md5(rand(-1239432, 23429304)));
        $saltedPassword = $encoder->encodePassword($password, $user->getSalt());

        $user->setUsername($name)
            ->setPassword($saltedPassword)
            ->save($this->propelConnection);

        $this->propelConnection->commit();
        return $this->forward('DTAMetadataBundle:Home:index',array());
    }*/
    //DEBUG END


    public function getDatabaseName(){
        $propelConf = \Propel::getConfiguration();
        //$this->messages[] = array("test" => print_r($propelConf, true));
        preg_match('/dbname=([^; ]+)/', $propelConf['datasources']['dtametadata']['connection']['dsn'], $matches);
        if($matches!=null and count($matches)>1){
            return $matches[1];
        }
        throw new Exception('Could not fetch database name from propel config.');
    }

    public function getDataseUser(){
        $propelConf = \Propel::getConfiguration();
        return $propelConf['datasources']['dtametadata']['connection']['user'];
    }
}
    
?>