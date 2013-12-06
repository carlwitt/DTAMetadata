<?php

namespace DTA\MetadataBundle\Model\Workflow\om;

use \BasePeer;
use \Criteria;
use \PDO;
use \PDOStatement;
use \Propel;
use \PropelException;
use \PropelPDO;
use DTA\MetadataBundle\Model\Data\PublicationPeer;
use DTA\MetadataBundle\Model\Workflow\LicensePeer;
use DTA\MetadataBundle\Model\Workflow\PartnerPeer;
use DTA\MetadataBundle\Model\Workflow\Textsource;
use DTA\MetadataBundle\Model\Workflow\TextsourcePeer;
use DTA\MetadataBundle\Model\Workflow\map\TextsourceTableMap;

abstract class BaseTextsourcePeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'dtametadata';

    /** the table name for this class */
    const TABLE_NAME = 'textsource';

    /** the related Propel class for this table */
    const OM_CLASS = 'DTA\\MetadataBundle\\Model\\Workflow\\Textsource';

    /** the related TableMap class for this table */
    const TM_CLASS = 'DTA\\MetadataBundle\\Model\\Workflow\\map\\TextsourceTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 8;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 8;

    /** the column name for the id field */
    const ID = 'textsource.id';

    /** the column name for the publication_id field */
    const PUBLICATION_ID = 'textsource.publication_id';

    /** the column name for the partner_id field */
    const PARTNER_ID = 'textsource.partner_id';

    /** the column name for the texturl field */
    const TEXTURL = 'textsource.texturl';

    /** the column name for the license_id field */
    const LICENSE_ID = 'textsource.license_id';

    /** the column name for the attribution field */
    const ATTRIBUTION = 'textsource.attribution';

    /** the column name for the created_at field */
    const CREATED_AT = 'textsource.created_at';

    /** the column name for the updated_at field */
    const UPDATED_AT = 'textsource.updated_at';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of Textsource objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array Textsource[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. TextsourcePeer::$fieldNames[TextsourcePeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('Id', 'PublicationId', 'PartnerId', 'Texturl', 'LicenseId', 'Attribution', 'CreatedAt', 'UpdatedAt', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('id', 'publicationId', 'partnerId', 'texturl', 'licenseId', 'attribution', 'createdAt', 'updatedAt', ),
        BasePeer::TYPE_COLNAME => array (TextsourcePeer::ID, TextsourcePeer::PUBLICATION_ID, TextsourcePeer::PARTNER_ID, TextsourcePeer::TEXTURL, TextsourcePeer::LICENSE_ID, TextsourcePeer::ATTRIBUTION, TextsourcePeer::CREATED_AT, TextsourcePeer::UPDATED_AT, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID', 'PUBLICATION_ID', 'PARTNER_ID', 'TEXTURL', 'LICENSE_ID', 'ATTRIBUTION', 'CREATED_AT', 'UPDATED_AT', ),
        BasePeer::TYPE_FIELDNAME => array ('id', 'publication_id', 'partner_id', 'texturl', 'license_id', 'attribution', 'created_at', 'updated_at', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. TextsourcePeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('Id' => 0, 'PublicationId' => 1, 'PartnerId' => 2, 'Texturl' => 3, 'LicenseId' => 4, 'Attribution' => 5, 'CreatedAt' => 6, 'UpdatedAt' => 7, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('id' => 0, 'publicationId' => 1, 'partnerId' => 2, 'texturl' => 3, 'licenseId' => 4, 'attribution' => 5, 'createdAt' => 6, 'updatedAt' => 7, ),
        BasePeer::TYPE_COLNAME => array (TextsourcePeer::ID => 0, TextsourcePeer::PUBLICATION_ID => 1, TextsourcePeer::PARTNER_ID => 2, TextsourcePeer::TEXTURL => 3, TextsourcePeer::LICENSE_ID => 4, TextsourcePeer::ATTRIBUTION => 5, TextsourcePeer::CREATED_AT => 6, TextsourcePeer::UPDATED_AT => 7, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID' => 0, 'PUBLICATION_ID' => 1, 'PARTNER_ID' => 2, 'TEXTURL' => 3, 'LICENSE_ID' => 4, 'ATTRIBUTION' => 5, 'CREATED_AT' => 6, 'UPDATED_AT' => 7, ),
        BasePeer::TYPE_FIELDNAME => array ('id' => 0, 'publication_id' => 1, 'partner_id' => 2, 'texturl' => 3, 'license_id' => 4, 'attribution' => 5, 'created_at' => 6, 'updated_at' => 7, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, )
    );

    /**
     * Translates a fieldname to another type
     *
     * @param      string $name field name
     * @param      string $fromType One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                         BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @param      string $toType   One of the class type constants
     * @return string          translated name of the field.
     * @throws PropelException - if the specified name could not be found in the fieldname mappings.
     */
    public static function translateFieldName($name, $fromType, $toType)
    {
        $toNames = TextsourcePeer::getFieldNames($toType);
        $key = isset(TextsourcePeer::$fieldKeys[$fromType][$name]) ? TextsourcePeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(TextsourcePeer::$fieldKeys[$fromType], true));
        }

        return $toNames[$key];
    }

    /**
     * Returns an array of field names.
     *
     * @param      string $type The type of fieldnames to return:
     *                      One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                      BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @return array           A list of field names
     * @throws PropelException - if the type is not valid.
     */
    public static function getFieldNames($type = BasePeer::TYPE_PHPNAME)
    {
        if (!array_key_exists($type, TextsourcePeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return TextsourcePeer::$fieldNames[$type];
    }

    /**
     * Convenience method which changes table.column to alias.column.
     *
     * Using this method you can maintain SQL abstraction while using column aliases.
     * <code>
     *		$c->addAlias("alias1", TablePeer::TABLE_NAME);
     *		$c->addJoin(TablePeer::alias("alias1", TablePeer::PRIMARY_KEY_COLUMN), TablePeer::PRIMARY_KEY_COLUMN);
     * </code>
     * @param      string $alias The alias for the current table.
     * @param      string $column The column name for current table. (i.e. TextsourcePeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(TextsourcePeer::TABLE_NAME.'.', $alias.'.', $column);
    }

    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param      Criteria $criteria object containing the columns to add.
     * @param      string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(TextsourcePeer::ID);
            $criteria->addSelectColumn(TextsourcePeer::PUBLICATION_ID);
            $criteria->addSelectColumn(TextsourcePeer::PARTNER_ID);
            $criteria->addSelectColumn(TextsourcePeer::TEXTURL);
            $criteria->addSelectColumn(TextsourcePeer::LICENSE_ID);
            $criteria->addSelectColumn(TextsourcePeer::ATTRIBUTION);
            $criteria->addSelectColumn(TextsourcePeer::CREATED_AT);
            $criteria->addSelectColumn(TextsourcePeer::UPDATED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.publication_id');
            $criteria->addSelectColumn($alias . '.partner_id');
            $criteria->addSelectColumn($alias . '.texturl');
            $criteria->addSelectColumn($alias . '.license_id');
            $criteria->addSelectColumn($alias . '.attribution');
            $criteria->addSelectColumn($alias . '.created_at');
            $criteria->addSelectColumn($alias . '.updated_at');
        }
    }

    /**
     * Returns the number of rows matching criteria.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @return int Number of matching rows.
     */
    public static function doCount(Criteria $criteria, $distinct = false, PropelPDO $con = null)
    {
        // we may modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            TextsourcePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        // BasePeer returns a PDOStatement
        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }
    /**
     * Selects one object from the DB.
     *
     * @param      Criteria $criteria object used to create the SELECT statement.
     * @param      PropelPDO $con
     * @return Textsource
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = TextsourcePeer::doSelect($critcopy, $con);
        if ($objects) {
            return $objects[0];
        }

        return null;
    }
    /**
     * Selects several row from the DB.
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con
     * @return array           Array of selected Objects
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelect(Criteria $criteria, PropelPDO $con = null)
    {
        return TextsourcePeer::populateObjects(TextsourcePeer::doSelectStmt($criteria, $con));
    }
    /**
     * Prepares the Criteria object and uses the parent doSelect() method to execute a PDOStatement.
     *
     * Use this method directly if you want to work with an executed statement directly (for example
     * to perform your own object hydration).
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con The connection to use
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return PDOStatement The executed PDOStatement object.
     * @see        BasePeer::doSelect()
     */
    public static function doSelectStmt(Criteria $criteria, PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            TextsourcePeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        // BasePeer returns a PDOStatement
        return BasePeer::doSelect($criteria, $con);
    }
    /**
     * Adds an object to the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doSelect*()
     * methods in your stub classes -- you may need to explicitly add objects
     * to the cache in order to ensure that the same objects are always returned by doSelect*()
     * and retrieveByPK*() calls.
     *
     * @param Textsource $obj A Textsource object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getId();
            } // if key === null
            TextsourcePeer::$instances[$key] = $obj;
        }
    }

    /**
     * Removes an object from the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doDelete
     * methods in your stub classes -- you may need to explicitly remove objects
     * from the cache in order to prevent returning objects that no longer exist.
     *
     * @param      mixed $value A Textsource object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof Textsource) {
                $key = (string) $value->getId();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or Textsource object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(TextsourcePeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return Textsource Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(TextsourcePeer::$instances[$key])) {
                return TextsourcePeer::$instances[$key];
            }
        }

        return null; // just to be explicit
    }

    /**
     * Clear the instance pool.
     *
     * @return void
     */
    public static function clearInstancePool($and_clear_all_references = false)
    {
      if ($and_clear_all_references) {
        foreach (TextsourcePeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        TextsourcePeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to textsource
     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return string A string version of PK or null if the components of primary key in result array are all null.
     */
    public static function getPrimaryKeyHashFromRow($row, $startcol = 0)
    {
        // If the PK cannot be derived from the row, return null.
        if ($row[$startcol] === null) {
            return null;
        }

        return (string) $row[$startcol];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $startcol = 0)
    {

        return (int) $row[$startcol];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function populateObjects(PDOStatement $stmt)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = TextsourcePeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = TextsourcePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = TextsourcePeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                TextsourcePeer::addInstanceToPool($obj, $key);
            } // if key exists
        }
        $stmt->closeCursor();

        return $results;
    }
    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return array (Textsource object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = TextsourcePeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = TextsourcePeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + TextsourcePeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = TextsourcePeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            TextsourcePeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }


    /**
     * Returns the number of rows matching criteria, joining the related Publication table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinPublication(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            TextsourcePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(TextsourcePeer::PUBLICATION_ID, PublicationPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related License table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinLicense(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            TextsourcePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(TextsourcePeer::LICENSE_ID, LicensePeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related Partner table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinPartner(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            TextsourcePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(TextsourcePeer::PARTNER_ID, PartnerPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of Textsource objects pre-filled with their Publication objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Textsource objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinPublication(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(TextsourcePeer::DATABASE_NAME);
        }

        TextsourcePeer::addSelectColumns($criteria);
        $startcol = TextsourcePeer::NUM_HYDRATE_COLUMNS;
        PublicationPeer::addSelectColumns($criteria);

        $criteria->addJoin(TextsourcePeer::PUBLICATION_ID, PublicationPeer::ID, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = TextsourcePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = TextsourcePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = TextsourcePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                TextsourcePeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = PublicationPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = PublicationPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = PublicationPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    PublicationPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (Textsource) to $obj2 (Publication)
                $obj2->addTextsource($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of Textsource objects pre-filled with their License objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Textsource objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinLicense(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(TextsourcePeer::DATABASE_NAME);
        }

        TextsourcePeer::addSelectColumns($criteria);
        $startcol = TextsourcePeer::NUM_HYDRATE_COLUMNS;
        LicensePeer::addSelectColumns($criteria);

        $criteria->addJoin(TextsourcePeer::LICENSE_ID, LicensePeer::ID, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = TextsourcePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = TextsourcePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = TextsourcePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                TextsourcePeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = LicensePeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = LicensePeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = LicensePeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    LicensePeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (Textsource) to $obj2 (License)
                $obj2->addTextsource($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of Textsource objects pre-filled with their Partner objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Textsource objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinPartner(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(TextsourcePeer::DATABASE_NAME);
        }

        TextsourcePeer::addSelectColumns($criteria);
        $startcol = TextsourcePeer::NUM_HYDRATE_COLUMNS;
        PartnerPeer::addSelectColumns($criteria);

        $criteria->addJoin(TextsourcePeer::PARTNER_ID, PartnerPeer::ID, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = TextsourcePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = TextsourcePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = TextsourcePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                TextsourcePeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = PartnerPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = PartnerPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = PartnerPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    PartnerPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (Textsource) to $obj2 (Partner)
                $obj2->addTextsource($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining all related tables
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAll(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            TextsourcePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(TextsourcePeer::PUBLICATION_ID, PublicationPeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::LICENSE_ID, LicensePeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::PARTNER_ID, PartnerPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }

    /**
     * Selects a collection of Textsource objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Textsource objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(TextsourcePeer::DATABASE_NAME);
        }

        TextsourcePeer::addSelectColumns($criteria);
        $startcol2 = TextsourcePeer::NUM_HYDRATE_COLUMNS;

        PublicationPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + PublicationPeer::NUM_HYDRATE_COLUMNS;

        LicensePeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + LicensePeer::NUM_HYDRATE_COLUMNS;

        PartnerPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + PartnerPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(TextsourcePeer::PUBLICATION_ID, PublicationPeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::LICENSE_ID, LicensePeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::PARTNER_ID, PartnerPeer::ID, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = TextsourcePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = TextsourcePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = TextsourcePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                TextsourcePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            // Add objects for joined Publication rows

            $key2 = PublicationPeer::getPrimaryKeyHashFromRow($row, $startcol2);
            if ($key2 !== null) {
                $obj2 = PublicationPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = PublicationPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    PublicationPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 loaded

                // Add the $obj1 (Textsource) to the collection in $obj2 (Publication)
                $obj2->addTextsource($obj1);
            } // if joined row not null

            // Add objects for joined License rows

            $key3 = LicensePeer::getPrimaryKeyHashFromRow($row, $startcol3);
            if ($key3 !== null) {
                $obj3 = LicensePeer::getInstanceFromPool($key3);
                if (!$obj3) {

                    $cls = LicensePeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    LicensePeer::addInstanceToPool($obj3, $key3);
                } // if obj3 loaded

                // Add the $obj1 (Textsource) to the collection in $obj3 (License)
                $obj3->addTextsource($obj1);
            } // if joined row not null

            // Add objects for joined Partner rows

            $key4 = PartnerPeer::getPrimaryKeyHashFromRow($row, $startcol4);
            if ($key4 !== null) {
                $obj4 = PartnerPeer::getInstanceFromPool($key4);
                if (!$obj4) {

                    $cls = PartnerPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    PartnerPeer::addInstanceToPool($obj4, $key4);
                } // if obj4 loaded

                // Add the $obj1 (Textsource) to the collection in $obj4 (Partner)
                $obj4->addTextsource($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining the related Publication table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptPublication(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            TextsourcePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(TextsourcePeer::LICENSE_ID, LicensePeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::PARTNER_ID, PartnerPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related License table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptLicense(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            TextsourcePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(TextsourcePeer::PUBLICATION_ID, PublicationPeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::PARTNER_ID, PartnerPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related Partner table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptPartner(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            TextsourcePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(TextsourcePeer::PUBLICATION_ID, PublicationPeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::LICENSE_ID, LicensePeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of Textsource objects pre-filled with all related objects except Publication.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Textsource objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptPublication(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(TextsourcePeer::DATABASE_NAME);
        }

        TextsourcePeer::addSelectColumns($criteria);
        $startcol2 = TextsourcePeer::NUM_HYDRATE_COLUMNS;

        LicensePeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + LicensePeer::NUM_HYDRATE_COLUMNS;

        PartnerPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + PartnerPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(TextsourcePeer::LICENSE_ID, LicensePeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::PARTNER_ID, PartnerPeer::ID, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = TextsourcePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = TextsourcePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = TextsourcePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                TextsourcePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined License rows

                $key2 = LicensePeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = LicensePeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = LicensePeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    LicensePeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (Textsource) to the collection in $obj2 (License)
                $obj2->addTextsource($obj1);

            } // if joined row is not null

                // Add objects for joined Partner rows

                $key3 = PartnerPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = PartnerPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = PartnerPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    PartnerPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (Textsource) to the collection in $obj3 (Partner)
                $obj3->addTextsource($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of Textsource objects pre-filled with all related objects except License.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Textsource objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptLicense(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(TextsourcePeer::DATABASE_NAME);
        }

        TextsourcePeer::addSelectColumns($criteria);
        $startcol2 = TextsourcePeer::NUM_HYDRATE_COLUMNS;

        PublicationPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + PublicationPeer::NUM_HYDRATE_COLUMNS;

        PartnerPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + PartnerPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(TextsourcePeer::PUBLICATION_ID, PublicationPeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::PARTNER_ID, PartnerPeer::ID, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = TextsourcePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = TextsourcePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = TextsourcePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                TextsourcePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined Publication rows

                $key2 = PublicationPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = PublicationPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = PublicationPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    PublicationPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (Textsource) to the collection in $obj2 (Publication)
                $obj2->addTextsource($obj1);

            } // if joined row is not null

                // Add objects for joined Partner rows

                $key3 = PartnerPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = PartnerPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = PartnerPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    PartnerPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (Textsource) to the collection in $obj3 (Partner)
                $obj3->addTextsource($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of Textsource objects pre-filled with all related objects except Partner.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Textsource objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptPartner(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(TextsourcePeer::DATABASE_NAME);
        }

        TextsourcePeer::addSelectColumns($criteria);
        $startcol2 = TextsourcePeer::NUM_HYDRATE_COLUMNS;

        PublicationPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + PublicationPeer::NUM_HYDRATE_COLUMNS;

        LicensePeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + LicensePeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(TextsourcePeer::PUBLICATION_ID, PublicationPeer::ID, $join_behavior);

        $criteria->addJoin(TextsourcePeer::LICENSE_ID, LicensePeer::ID, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = TextsourcePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = TextsourcePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = TextsourcePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                TextsourcePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined Publication rows

                $key2 = PublicationPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = PublicationPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = PublicationPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    PublicationPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (Textsource) to the collection in $obj2 (Publication)
                $obj2->addTextsource($obj1);

            } // if joined row is not null

                // Add objects for joined License rows

                $key3 = LicensePeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = LicensePeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = LicensePeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    LicensePeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (Textsource) to the collection in $obj3 (License)
                $obj3->addTextsource($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }

    /**
     * Returns the TableMap related to this peer.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getDatabaseMap(TextsourcePeer::DATABASE_NAME)->getTable(TextsourcePeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseTextsourcePeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseTextsourcePeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \DTA\MetadataBundle\Model\Workflow\map\TextsourceTableMap());
      }
    }

    /**
     * The class that the Peer will make instances of.
     *
     *
     * @return string ClassName
     */
    public static function getOMClass($row = 0, $colnum = 0)
    {
        return TextsourcePeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a Textsource or Criteria object.
     *
     * @param      mixed $values Criteria or Textsource object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from Textsource object
        }

        if ($criteria->containsKey(TextsourcePeer::ID) && $criteria->keyContainsValue(TextsourcePeer::ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.TextsourcePeer::ID.')');
        }


        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = BasePeer::doInsert($criteria, $con);
            $con->commit();
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

    /**
     * Performs an UPDATE on the database, given a Textsource or Criteria object.
     *
     * @param      mixed $values Criteria or Textsource object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(TextsourcePeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(TextsourcePeer::ID);
            $value = $criteria->remove(TextsourcePeer::ID);
            if ($value) {
                $selectCriteria->add(TextsourcePeer::ID, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(TextsourcePeer::TABLE_NAME);
            }

        } else { // $values is Textsource object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the textsource table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(TextsourcePeer::TABLE_NAME, $con, TextsourcePeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            TextsourcePeer::clearInstancePool();
            TextsourcePeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a Textsource or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or Textsource object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param      PropelPDO $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *				if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, PropelPDO $con = null)
     {
        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            TextsourcePeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof Textsource) { // it's a model object
            // invalidate the cache for this single object
            TextsourcePeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(TextsourcePeer::DATABASE_NAME);
            $criteria->add(TextsourcePeer::ID, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                TextsourcePeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(TextsourcePeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            TextsourcePeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given Textsource object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param Textsource $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(TextsourcePeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(TextsourcePeer::TABLE_NAME);

            if (! is_array($cols)) {
                $cols = array($cols);
            }

            foreach ($cols as $colName) {
                if ($tableMap->hasColumn($colName)) {
                    $get = 'get' . $tableMap->getColumn($colName)->getPhpName();
                    $columns[$colName] = $obj->$get();
                }
            }
        } else {

        }

        return BasePeer::doValidate(TextsourcePeer::DATABASE_NAME, TextsourcePeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return Textsource
     */
    public static function retrieveByPK($pk, PropelPDO $con = null)
    {

        if (null !== ($obj = TextsourcePeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(TextsourcePeer::DATABASE_NAME);
        $criteria->add(TextsourcePeer::ID, $pk);

        $v = TextsourcePeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return Textsource[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(TextsourcePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(TextsourcePeer::DATABASE_NAME);
            $criteria->add(TextsourcePeer::ID, $pks, Criteria::IN);
            $objs = TextsourcePeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseTextsourcePeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseTextsourcePeer::buildTableMap();

