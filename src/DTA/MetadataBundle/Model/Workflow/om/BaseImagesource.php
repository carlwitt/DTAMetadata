<?php

namespace DTA\MetadataBundle\Model\Workflow\om;

use \BaseObject;
use \BasePeer;
use \Criteria;
use \Exception;
use \PDO;
use \Persistent;
use \Propel;
use \PropelException;
use \PropelPDO;
use DTA\MetadataBundle\Model\Data\Publication;
use DTA\MetadataBundle\Model\Data\PublicationQuery;
use DTA\MetadataBundle\Model\Workflow\Imagesource;
use DTA\MetadataBundle\Model\Workflow\ImagesourcePeer;
use DTA\MetadataBundle\Model\Workflow\ImagesourceQuery;
use DTA\MetadataBundle\Model\Workflow\License;
use DTA\MetadataBundle\Model\Workflow\LicenseQuery;
use DTA\MetadataBundle\Model\Workflow\Partner;
use DTA\MetadataBundle\Model\Workflow\PartnerQuery;

abstract class BaseImagesource extends BaseObject implements Persistent, \DTA\MetadataBundle\Model\table_row_view\TableRowViewInterface
{
    /**
     * Peer class name
     */
    const PEER = 'DTA\\MetadataBundle\\Model\\Workflow\\ImagesourcePeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        ImagesourcePeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinit loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the publication_id field.
     * @var        int
     */
    protected $publication_id;

    /**
     * The value for the partner_id field.
     * @var        int
     */
    protected $partner_id;

    /**
     * The value for the cataloguesignature field.
     * @var        string
     */
    protected $cataloguesignature;

    /**
     * The value for the catalogueurl field.
     * @var        string
     */
    protected $catalogueurl;

    /**
     * The value for the numfaksimiles field.
     * @var        int
     */
    protected $numfaksimiles;

    /**
     * The value for the extentasofcatalogue field.
     * @var        string
     */
    protected $extentasofcatalogue;

    /**
     * The value for the faksimilerefrange field.
     * @var        string
     */
    protected $faksimilerefrange;

    /**
     * The value for the originalrefrange field.
     * @var        string
     */
    protected $originalrefrange;

    /**
     * The value for the imageurl field.
     * @var        string
     */
    protected $imageurl;

    /**
     * The value for the imageurn field.
     * @var        string
     */
    protected $imageurn;

    /**
     * The value for the license_id field.
     * @var        int
     */
    protected $license_id;

    /**
     * @var        Publication
     */
    protected $aPublication;

    /**
     * @var        License
     */
    protected $aLicense;

    /**
     * @var        Partner
     */
    protected $aPartner;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Flag to prevent endless clearAllReferences($deep=true) loop, if this object is referenced
     * @var        boolean
     */
    protected $alreadyInClearAllReferencesDeep = false;

    // table_row_view behavior
    public static $tableRowViewCaptions = array('Id', 'PublicationId', 'PartnerId', 'Cataloguesignature', 'Catalogueurl', 'Numfaksimiles', 'Extentasofcatalogue', 'Faksimilerefrange', 'Originalrefrange', 'Imageurl', 'Imageurn', 'LicenseId', );	public   $tableRowViewAccessors = array('Id'=>'Id', 'PublicationId'=>'PublicationId', 'PartnerId'=>'PartnerId', 'Cataloguesignature'=>'Cataloguesignature', 'Catalogueurl'=>'Catalogueurl', 'Numfaksimiles'=>'Numfaksimiles', 'Extentasofcatalogue'=>'Extentasofcatalogue', 'Faksimilerefrange'=>'Faksimilerefrange', 'Originalrefrange'=>'Originalrefrange', 'Imageurl'=>'Imageurl', 'Imageurn'=>'Imageurn', 'LicenseId'=>'LicenseId', );
    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [publication_id] column value.
     *
     * @return int
     */
    public function getPublicationId()
    {
        return $this->publication_id;
    }

    /**
     * Get the [partner_id] column value.
     * Anbieter Leitdruck
     * @return int
     */
    public function getPartnerId()
    {
        return $this->partner_id;
    }

    /**
     * Get the [cataloguesignature] column value.
     *
     * @return string
     */
    public function getCataloguesignature()
    {
        return $this->cataloguesignature;
    }

    /**
     * Get the [catalogueurl] column value.
     * Link in den Katalog
     * @return string
     */
    public function getCatalogueurl()
    {
        return $this->catalogueurl;
    }

    /**
     * Get the [numfaksimiles] column value.
     * Anzahl Faksimiles
     * @return int
     */
    public function getNumfaksimiles()
    {
        return $this->numfaksimiles;
    }

    /**
     * Get the [extentasofcatalogue] column value.
     * Umfang laut Katalog
     * @return string
     */
    public function getExtentasofcatalogue()
    {
        return $this->extentasofcatalogue;
    }

    /**
     * Get the [faksimilerefrange] column value.
     * Referenzierte Faksimileseitenzahlen
     * @return string
     */
    public function getFaksimilerefrange()
    {
        return $this->faksimilerefrange;
    }

    /**
     * Get the [originalrefrange] column value.
     * Referenzierte Originalseitenzahlen
     * @return string
     */
    public function getOriginalrefrange()
    {
        return $this->originalrefrange;
    }

    /**
     * Get the [imageurl] column value.
     * URL der Bilddigitalisate
     * @return string
     */
    public function getImageurl()
    {
        return $this->imageurl;
    }

    /**
     * Get the [imageurn] column value.
     * URN der Bilddigitalisate
     * @return string
     */
    public function getImageurn()
    {
        return $this->imageurn;
    }

    /**
     * Get the [license_id] column value.
     * Lizenz
     * @return int
     */
    public function getLicenseId()
    {
        return $this->license_id;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = ImagesourcePeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [publication_id] column.
     *
     * @param int $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setPublicationId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->publication_id !== $v) {
            $this->publication_id = $v;
            $this->modifiedColumns[] = ImagesourcePeer::PUBLICATION_ID;
        }

        if ($this->aPublication !== null && $this->aPublication->getId() !== $v) {
            $this->aPublication = null;
        }


        return $this;
    } // setPublicationId()

    /**
     * Set the value of [partner_id] column.
     * Anbieter Leitdruck
     * @param int $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setPartnerId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->partner_id !== $v) {
            $this->partner_id = $v;
            $this->modifiedColumns[] = ImagesourcePeer::PARTNER_ID;
        }

        if ($this->aPartner !== null && $this->aPartner->getId() !== $v) {
            $this->aPartner = null;
        }


        return $this;
    } // setPartnerId()

    /**
     * Set the value of [cataloguesignature] column.
     *
     * @param string $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setCataloguesignature($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->cataloguesignature !== $v) {
            $this->cataloguesignature = $v;
            $this->modifiedColumns[] = ImagesourcePeer::CATALOGUESIGNATURE;
        }


        return $this;
    } // setCataloguesignature()

    /**
     * Set the value of [catalogueurl] column.
     * Link in den Katalog
     * @param string $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setCatalogueurl($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->catalogueurl !== $v) {
            $this->catalogueurl = $v;
            $this->modifiedColumns[] = ImagesourcePeer::CATALOGUEURL;
        }


        return $this;
    } // setCatalogueurl()

    /**
     * Set the value of [numfaksimiles] column.
     * Anzahl Faksimiles
     * @param int $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setNumfaksimiles($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->numfaksimiles !== $v) {
            $this->numfaksimiles = $v;
            $this->modifiedColumns[] = ImagesourcePeer::NUMFAKSIMILES;
        }


        return $this;
    } // setNumfaksimiles()

    /**
     * Set the value of [extentasofcatalogue] column.
     * Umfang laut Katalog
     * @param string $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setExtentasofcatalogue($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->extentasofcatalogue !== $v) {
            $this->extentasofcatalogue = $v;
            $this->modifiedColumns[] = ImagesourcePeer::EXTENTASOFCATALOGUE;
        }


        return $this;
    } // setExtentasofcatalogue()

    /**
     * Set the value of [faksimilerefrange] column.
     * Referenzierte Faksimileseitenzahlen
     * @param string $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setFaksimilerefrange($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->faksimilerefrange !== $v) {
            $this->faksimilerefrange = $v;
            $this->modifiedColumns[] = ImagesourcePeer::FAKSIMILEREFRANGE;
        }


        return $this;
    } // setFaksimilerefrange()

    /**
     * Set the value of [originalrefrange] column.
     * Referenzierte Originalseitenzahlen
     * @param string $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setOriginalrefrange($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->originalrefrange !== $v) {
            $this->originalrefrange = $v;
            $this->modifiedColumns[] = ImagesourcePeer::ORIGINALREFRANGE;
        }


        return $this;
    } // setOriginalrefrange()

    /**
     * Set the value of [imageurl] column.
     * URL der Bilddigitalisate
     * @param string $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setImageurl($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->imageurl !== $v) {
            $this->imageurl = $v;
            $this->modifiedColumns[] = ImagesourcePeer::IMAGEURL;
        }


        return $this;
    } // setImageurl()

    /**
     * Set the value of [imageurn] column.
     * URN der Bilddigitalisate
     * @param string $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setImageurn($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->imageurn !== $v) {
            $this->imageurn = $v;
            $this->modifiedColumns[] = ImagesourcePeer::IMAGEURN;
        }


        return $this;
    } // setImageurn()

    /**
     * Set the value of [license_id] column.
     * Lizenz
     * @param int $v new value
     * @return Imagesource The current object (for fluent API support)
     */
    public function setLicenseId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->license_id !== $v) {
            $this->license_id = $v;
            $this->modifiedColumns[] = ImagesourcePeer::LICENSE_ID;
        }

        if ($this->aLicense !== null && $this->aLicense->getId() !== $v) {
            $this->aLicense = null;
        }


        return $this;
    } // setLicenseId()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return true
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
     * @param int $startcol 0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false)
    {
        try {

            $this->id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->publication_id = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
            $this->partner_id = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
            $this->cataloguesignature = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->catalogueurl = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->numfaksimiles = ($row[$startcol + 5] !== null) ? (int) $row[$startcol + 5] : null;
            $this->extentasofcatalogue = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->faksimilerefrange = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->originalrefrange = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->imageurl = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->imageurn = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
            $this->license_id = ($row[$startcol + 11] !== null) ? (int) $row[$startcol + 11] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);
            return $startcol + 12; // 12 = ImagesourcePeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating Imagesource object", $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {

        if ($this->aPublication !== null && $this->publication_id !== $this->aPublication->getId()) {
            $this->aPublication = null;
        }
        if ($this->aPartner !== null && $this->partner_id !== $this->aPartner->getId()) {
            $this->aPartner = null;
        }
        if ($this->aLicense !== null && $this->license_id !== $this->aLicense->getId()) {
            $this->aLicense = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param boolean $deep (optional) Whether to also de-associated any related objects.
     * @param PropelPDO $con (optional) The PropelPDO connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getConnection(ImagesourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = ImagesourcePeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aPublication = null;
            $this->aLicense = null;
            $this->aPartner = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param PropelPDO $con
     * @return void
     * @throws PropelException
     * @throws Exception
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete(PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(ImagesourcePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ImagesourceQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @throws Exception
     * @see        doSave()
     */
    public function save(PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(ImagesourcePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                ImagesourcePeer::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see        save()
     */
    protected function doSave(PropelPDO $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their coresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aPublication !== null) {
                if ($this->aPublication->isModified() || $this->aPublication->isNew()) {
                    $affectedRows += $this->aPublication->save($con);
                }
                $this->setPublication($this->aPublication);
            }

            if ($this->aLicense !== null) {
                if ($this->aLicense->isModified() || $this->aLicense->isNew()) {
                    $affectedRows += $this->aLicense->save($con);
                }
                $this->setLicense($this->aLicense);
            }

            if ($this->aPartner !== null) {
                if ($this->aPartner->isModified() || $this->aPartner->isNew()) {
                    $affectedRows += $this->aPartner->save($con);
                }
                $this->setPartner($this->aPartner);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param PropelPDO $con
     *
     * @throws PropelException
     * @see        doSave()
     */
    protected function doInsert(PropelPDO $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[] = ImagesourcePeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ImagesourcePeer::ID . ')');
        }
        if (null === $this->id) {
            try {
                $stmt = $con->query("SELECT nextval('imagesource_id_seq')");
                $row = $stmt->fetch(PDO::FETCH_NUM);
                $this->id = $row[0];
            } catch (Exception $e) {
                throw new PropelException('Unable to get sequence id.', $e);
            }
        }


         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ImagesourcePeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '"id"';
        }
        if ($this->isColumnModified(ImagesourcePeer::PUBLICATION_ID)) {
            $modifiedColumns[':p' . $index++]  = '"publication_id"';
        }
        if ($this->isColumnModified(ImagesourcePeer::PARTNER_ID)) {
            $modifiedColumns[':p' . $index++]  = '"partner_id"';
        }
        if ($this->isColumnModified(ImagesourcePeer::CATALOGUESIGNATURE)) {
            $modifiedColumns[':p' . $index++]  = '"cataloguesignature"';
        }
        if ($this->isColumnModified(ImagesourcePeer::CATALOGUEURL)) {
            $modifiedColumns[':p' . $index++]  = '"catalogueurl"';
        }
        if ($this->isColumnModified(ImagesourcePeer::NUMFAKSIMILES)) {
            $modifiedColumns[':p' . $index++]  = '"numfaksimiles"';
        }
        if ($this->isColumnModified(ImagesourcePeer::EXTENTASOFCATALOGUE)) {
            $modifiedColumns[':p' . $index++]  = '"extentasofcatalogue"';
        }
        if ($this->isColumnModified(ImagesourcePeer::FAKSIMILEREFRANGE)) {
            $modifiedColumns[':p' . $index++]  = '"faksimilerefrange"';
        }
        if ($this->isColumnModified(ImagesourcePeer::ORIGINALREFRANGE)) {
            $modifiedColumns[':p' . $index++]  = '"originalrefrange"';
        }
        if ($this->isColumnModified(ImagesourcePeer::IMAGEURL)) {
            $modifiedColumns[':p' . $index++]  = '"imageurl"';
        }
        if ($this->isColumnModified(ImagesourcePeer::IMAGEURN)) {
            $modifiedColumns[':p' . $index++]  = '"imageurn"';
        }
        if ($this->isColumnModified(ImagesourcePeer::LICENSE_ID)) {
            $modifiedColumns[':p' . $index++]  = '"license_id"';
        }

        $sql = sprintf(
            'INSERT INTO "imagesource" (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '"id"':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case '"publication_id"':
                        $stmt->bindValue($identifier, $this->publication_id, PDO::PARAM_INT);
                        break;
                    case '"partner_id"':
                        $stmt->bindValue($identifier, $this->partner_id, PDO::PARAM_INT);
                        break;
                    case '"cataloguesignature"':
                        $stmt->bindValue($identifier, $this->cataloguesignature, PDO::PARAM_STR);
                        break;
                    case '"catalogueurl"':
                        $stmt->bindValue($identifier, $this->catalogueurl, PDO::PARAM_STR);
                        break;
                    case '"numfaksimiles"':
                        $stmt->bindValue($identifier, $this->numfaksimiles, PDO::PARAM_INT);
                        break;
                    case '"extentasofcatalogue"':
                        $stmt->bindValue($identifier, $this->extentasofcatalogue, PDO::PARAM_STR);
                        break;
                    case '"faksimilerefrange"':
                        $stmt->bindValue($identifier, $this->faksimilerefrange, PDO::PARAM_STR);
                        break;
                    case '"originalrefrange"':
                        $stmt->bindValue($identifier, $this->originalrefrange, PDO::PARAM_STR);
                        break;
                    case '"imageurl"':
                        $stmt->bindValue($identifier, $this->imageurl, PDO::PARAM_STR);
                        break;
                    case '"imageurn"':
                        $stmt->bindValue($identifier, $this->imageurn, PDO::PARAM_STR);
                        break;
                    case '"license_id"':
                        $stmt->bindValue($identifier, $this->license_id, PDO::PARAM_INT);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), $e);
        }

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param PropelPDO $con
     *
     * @see        doSave()
     */
    protected function doUpdate(PropelPDO $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();
        BasePeer::doUpdate($selectCriteria, $valuesCriteria, $con);
    }

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param mixed $columns Column name or an array of column names.
     * @return boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();

            return true;
        }

        $this->validationFailures = $res;

        return false;
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggreagated array of ValidationFailed objects will be returned.
     *
     * @param array $columns Array of column names to validate.
     * @return mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objets otherwise.
     */
    protected function doValidate($columns = null)
    {
        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            // We call the validate method on the following object(s) if they
            // were passed to this object by their coresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aPublication !== null) {
                if (!$this->aPublication->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aPublication->getValidationFailures());
                }
            }

            if ($this->aLicense !== null) {
                if (!$this->aLicense->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aLicense->getValidationFailures());
                }
            }

            if ($this->aPartner !== null) {
                if (!$this->aPartner->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aPartner->getValidationFailures());
                }
            }


            if (($retval = ImagesourcePeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }



            $this->alreadyInValidation = false;
        }

        return (!empty($failureMap) ? $failureMap : true);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param string $name name
     * @param string $type The type of fieldname the $name is of:
     *               one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *               BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *               Defaults to BasePeer::TYPE_PHPNAME
     * @return mixed Value of field.
     */
    public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = ImagesourcePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getPublicationId();
                break;
            case 2:
                return $this->getPartnerId();
                break;
            case 3:
                return $this->getCataloguesignature();
                break;
            case 4:
                return $this->getCatalogueurl();
                break;
            case 5:
                return $this->getNumfaksimiles();
                break;
            case 6:
                return $this->getExtentasofcatalogue();
                break;
            case 7:
                return $this->getFaksimilerefrange();
                break;
            case 8:
                return $this->getOriginalrefrange();
                break;
            case 9:
                return $this->getImageurl();
                break;
            case 10:
                return $this->getImageurn();
                break;
            case 11:
                return $this->getLicenseId();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     *                    BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                    Defaults to BasePeer::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to true.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['Imagesource'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Imagesource'][$this->getPrimaryKey()] = true;
        $keys = ImagesourcePeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getPublicationId(),
            $keys[2] => $this->getPartnerId(),
            $keys[3] => $this->getCataloguesignature(),
            $keys[4] => $this->getCatalogueurl(),
            $keys[5] => $this->getNumfaksimiles(),
            $keys[6] => $this->getExtentasofcatalogue(),
            $keys[7] => $this->getFaksimilerefrange(),
            $keys[8] => $this->getOriginalrefrange(),
            $keys[9] => $this->getImageurl(),
            $keys[10] => $this->getImageurn(),
            $keys[11] => $this->getLicenseId(),
        );
        if ($includeForeignObjects) {
            if (null !== $this->aPublication) {
                $result['Publication'] = $this->aPublication->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aLicense) {
                $result['License'] = $this->aLicense->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aPartner) {
                $result['Partner'] = $this->aPartner->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param string $name peer name
     * @param mixed $value field value
     * @param string $type The type of fieldname the $name is of:
     *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                     Defaults to BasePeer::TYPE_PHPNAME
     * @return void
     */
    public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = ImagesourcePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

        $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @param mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setPublicationId($value);
                break;
            case 2:
                $this->setPartnerId($value);
                break;
            case 3:
                $this->setCataloguesignature($value);
                break;
            case 4:
                $this->setCatalogueurl($value);
                break;
            case 5:
                $this->setNumfaksimiles($value);
                break;
            case 6:
                $this->setExtentasofcatalogue($value);
                break;
            case 7:
                $this->setFaksimilerefrange($value);
                break;
            case 8:
                $this->setOriginalrefrange($value);
                break;
            case 9:
                $this->setImageurl($value);
                break;
            case 10:
                $this->setImageurn($value);
                break;
            case 11:
                $this->setLicenseId($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     * The default key type is the column's BasePeer::TYPE_PHPNAME
     *
     * @param array  $arr     An array to populate the object from.
     * @param string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = ImagesourcePeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setPublicationId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setPartnerId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setCataloguesignature($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setCatalogueurl($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setNumfaksimiles($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setExtentasofcatalogue($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setFaksimilerefrange($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setOriginalrefrange($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setImageurl($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setImageurn($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setLicenseId($arr[$keys[11]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ImagesourcePeer::DATABASE_NAME);

        if ($this->isColumnModified(ImagesourcePeer::ID)) $criteria->add(ImagesourcePeer::ID, $this->id);
        if ($this->isColumnModified(ImagesourcePeer::PUBLICATION_ID)) $criteria->add(ImagesourcePeer::PUBLICATION_ID, $this->publication_id);
        if ($this->isColumnModified(ImagesourcePeer::PARTNER_ID)) $criteria->add(ImagesourcePeer::PARTNER_ID, $this->partner_id);
        if ($this->isColumnModified(ImagesourcePeer::CATALOGUESIGNATURE)) $criteria->add(ImagesourcePeer::CATALOGUESIGNATURE, $this->cataloguesignature);
        if ($this->isColumnModified(ImagesourcePeer::CATALOGUEURL)) $criteria->add(ImagesourcePeer::CATALOGUEURL, $this->catalogueurl);
        if ($this->isColumnModified(ImagesourcePeer::NUMFAKSIMILES)) $criteria->add(ImagesourcePeer::NUMFAKSIMILES, $this->numfaksimiles);
        if ($this->isColumnModified(ImagesourcePeer::EXTENTASOFCATALOGUE)) $criteria->add(ImagesourcePeer::EXTENTASOFCATALOGUE, $this->extentasofcatalogue);
        if ($this->isColumnModified(ImagesourcePeer::FAKSIMILEREFRANGE)) $criteria->add(ImagesourcePeer::FAKSIMILEREFRANGE, $this->faksimilerefrange);
        if ($this->isColumnModified(ImagesourcePeer::ORIGINALREFRANGE)) $criteria->add(ImagesourcePeer::ORIGINALREFRANGE, $this->originalrefrange);
        if ($this->isColumnModified(ImagesourcePeer::IMAGEURL)) $criteria->add(ImagesourcePeer::IMAGEURL, $this->imageurl);
        if ($this->isColumnModified(ImagesourcePeer::IMAGEURN)) $criteria->add(ImagesourcePeer::IMAGEURN, $this->imageurn);
        if ($this->isColumnModified(ImagesourcePeer::LICENSE_ID)) $criteria->add(ImagesourcePeer::LICENSE_ID, $this->license_id);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(ImagesourcePeer::DATABASE_NAME);
        $criteria->add(ImagesourcePeer::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of Imagesource (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setPublicationId($this->getPublicationId());
        $copyObj->setPartnerId($this->getPartnerId());
        $copyObj->setCataloguesignature($this->getCataloguesignature());
        $copyObj->setCatalogueurl($this->getCatalogueurl());
        $copyObj->setNumfaksimiles($this->getNumfaksimiles());
        $copyObj->setExtentasofcatalogue($this->getExtentasofcatalogue());
        $copyObj->setFaksimilerefrange($this->getFaksimilerefrange());
        $copyObj->setOriginalrefrange($this->getOriginalrefrange());
        $copyObj->setImageurl($this->getImageurl());
        $copyObj->setImageurn($this->getImageurn());
        $copyObj->setLicenseId($this->getLicenseId());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            //unflag object copy
            $this->startCopy = false;
        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return Imagesource Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return ImagesourcePeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new ImagesourcePeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a Publication object.
     *
     * @param             Publication $v
     * @return Imagesource The current object (for fluent API support)
     * @throws PropelException
     */
    public function setPublication(Publication $v = null)
    {
        if ($v === null) {
            $this->setPublicationId(NULL);
        } else {
            $this->setPublicationId($v->getId());
        }

        $this->aPublication = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the Publication object, it will not be re-added.
        if ($v !== null) {
            $v->addImagesource($this);
        }


        return $this;
    }


    /**
     * Get the associated Publication object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return Publication The associated Publication object.
     * @throws PropelException
     */
    public function getPublication(PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aPublication === null && ($this->publication_id !== null) && $doQuery) {
            $this->aPublication = PublicationQuery::create()->findPk($this->publication_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aPublication->addImagesources($this);
             */
        }

        return $this->aPublication;
    }

    /**
     * Declares an association between this object and a License object.
     *
     * @param             License $v
     * @return Imagesource The current object (for fluent API support)
     * @throws PropelException
     */
    public function setLicense(License $v = null)
    {
        if ($v === null) {
            $this->setLicenseId(NULL);
        } else {
            $this->setLicenseId($v->getId());
        }

        $this->aLicense = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the License object, it will not be re-added.
        if ($v !== null) {
            $v->addImagesource($this);
        }


        return $this;
    }


    /**
     * Get the associated License object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return License The associated License object.
     * @throws PropelException
     */
    public function getLicense(PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aLicense === null && ($this->license_id !== null) && $doQuery) {
            $this->aLicense = LicenseQuery::create()->findPk($this->license_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aLicense->addImagesources($this);
             */
        }

        return $this->aLicense;
    }

    /**
     * Declares an association between this object and a Partner object.
     *
     * @param             Partner $v
     * @return Imagesource The current object (for fluent API support)
     * @throws PropelException
     */
    public function setPartner(Partner $v = null)
    {
        if ($v === null) {
            $this->setPartnerId(NULL);
        } else {
            $this->setPartnerId($v->getId());
        }

        $this->aPartner = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the Partner object, it will not be re-added.
        if ($v !== null) {
            $v->addImagesource($this);
        }


        return $this;
    }


    /**
     * Get the associated Partner object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return Partner The associated Partner object.
     * @throws PropelException
     */
    public function getPartner(PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aPartner === null && ($this->partner_id !== null) && $doQuery) {
            $this->aPartner = PartnerQuery::create()->findPk($this->partner_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aPartner->addImagesources($this);
             */
        }

        return $this->aPartner;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->publication_id = null;
        $this->partner_id = null;
        $this->cataloguesignature = null;
        $this->catalogueurl = null;
        $this->numfaksimiles = null;
        $this->extentasofcatalogue = null;
        $this->faksimilerefrange = null;
        $this->originalrefrange = null;
        $this->imageurl = null;
        $this->imageurn = null;
        $this->license_id = null;
        $this->alreadyInSave = false;
        $this->alreadyInValidation = false;
        $this->alreadyInClearAllReferencesDeep = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volumne/high-memory operations.
     *
     * @param boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep && !$this->alreadyInClearAllReferencesDeep) {
            $this->alreadyInClearAllReferencesDeep = true;
            if ($this->aPublication instanceof Persistent) {
              $this->aPublication->clearAllReferences($deep);
            }
            if ($this->aLicense instanceof Persistent) {
              $this->aLicense->clearAllReferences($deep);
            }
            if ($this->aPartner instanceof Persistent) {
              $this->aPartner->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        $this->aPublication = null;
        $this->aLicense = null;
        $this->aPartner = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ImagesourcePeer::DEFAULT_STRING_FORMAT);
    }

    /**
     * return true is the object is in saving state
     *
     * @return boolean
     */
    public function isAlreadyInSave()
    {
        return $this->alreadyInSave;
    }

    // table_row_view behavior
    /**
     * To specify which columns are to be visible in the user display
     * (In the view that lists all database records of a class as a table)
     */
    public static function getTableViewColumnNames(){
        $rc = new \ReflectionClass(get_called_class());
        return $rc->getStaticPropertyValue("tableRowViewCaptions");
    }

    /**
     * To access the data using the specified column names.
     * @param string columnName
     */
    public function getAttributeByTableViewColumName($columnName){

        $accessor = $this->tableRowViewAccessors[$columnName];

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


}
