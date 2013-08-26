<?php

namespace DTA\MetadataBundle\Model\Classification\om;

use \Criteria;
use \Exception;
use \ModelCriteria;
use \ModelJoin;
use \PDO;
use \Propel;
use \PropelCollection;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use DTA\MetadataBundle\Model\Classification\Genre;
use DTA\MetadataBundle\Model\Classification\GenrePeer;
use DTA\MetadataBundle\Model\Classification\GenreQuery;
use DTA\MetadataBundle\Model\Data\Work;
use DTA\MetadataBundle\Model\Master\GenreWork;

/**
 * @method GenreQuery orderById($order = Criteria::ASC) Order by the id column
 * @method GenreQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method GenreQuery orderByChildof($order = Criteria::ASC) Order by the childof column
 *
 * @method GenreQuery groupById() Group by the id column
 * @method GenreQuery groupByName() Group by the name column
 * @method GenreQuery groupByChildof() Group by the childof column
 *
 * @method GenreQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method GenreQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method GenreQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method GenreQuery leftJoinGenreRelatedByChildof($relationAlias = null) Adds a LEFT JOIN clause to the query using the GenreRelatedByChildof relation
 * @method GenreQuery rightJoinGenreRelatedByChildof($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GenreRelatedByChildof relation
 * @method GenreQuery innerJoinGenreRelatedByChildof($relationAlias = null) Adds a INNER JOIN clause to the query using the GenreRelatedByChildof relation
 *
 * @method GenreQuery leftJoinGenreRelatedById($relationAlias = null) Adds a LEFT JOIN clause to the query using the GenreRelatedById relation
 * @method GenreQuery rightJoinGenreRelatedById($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GenreRelatedById relation
 * @method GenreQuery innerJoinGenreRelatedById($relationAlias = null) Adds a INNER JOIN clause to the query using the GenreRelatedById relation
 *
 * @method GenreQuery leftJoinGenreWork($relationAlias = null) Adds a LEFT JOIN clause to the query using the GenreWork relation
 * @method GenreQuery rightJoinGenreWork($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GenreWork relation
 * @method GenreQuery innerJoinGenreWork($relationAlias = null) Adds a INNER JOIN clause to the query using the GenreWork relation
 *
 * @method Genre findOne(PropelPDO $con = null) Return the first Genre matching the query
 * @method Genre findOneOrCreate(PropelPDO $con = null) Return the first Genre matching the query, or a new Genre object populated from the query conditions when no match is found
 *
 * @method Genre findOneByName(string $name) Return the first Genre filtered by the name column
 * @method Genre findOneByChildof(int $childof) Return the first Genre filtered by the childof column
 *
 * @method array findById(int $id) Return Genre objects filtered by the id column
 * @method array findByName(string $name) Return Genre objects filtered by the name column
 * @method array findByChildof(int $childof) Return Genre objects filtered by the childof column
 */
abstract class BaseGenreQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseGenreQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'DTAMetadata', $modelName = 'DTA\\MetadataBundle\\Model\\Classification\\Genre', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new GenreQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   GenreQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return GenreQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof GenreQuery) {
            return $criteria;
        }
        $query = new GenreQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return   Genre|Genre[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = GenrePeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(GenrePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Alias of findPk to use instance pooling
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 Genre A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneById($key, $con = null)
     {
        return $this->findPk($key, $con);
     }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 Genre A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT "id", "name", "childof" FROM "genre" WHERE "id" = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new Genre();
            $obj->hydrate($row);
            GenrePeer::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return Genre|Genre[]|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return PropelObjectCollection|Genre[]|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection($this->getDbName(), Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($stmt);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GenrePeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GenrePeer::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id >= 12
     * $query->filterById(array('max' => 12)); // WHERE id <= 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(GenrePeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(GenrePeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GenrePeer::ID, $id, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%'); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $name)) {
                $name = str_replace('*', '%', $name);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GenrePeer::NAME, $name, $comparison);
    }

    /**
     * Filter the query on the childof column
     *
     * Example usage:
     * <code>
     * $query->filterByChildof(1234); // WHERE childof = 1234
     * $query->filterByChildof(array(12, 34)); // WHERE childof IN (12, 34)
     * $query->filterByChildof(array('min' => 12)); // WHERE childof >= 12
     * $query->filterByChildof(array('max' => 12)); // WHERE childof <= 12
     * </code>
     *
     * @see       filterByGenreRelatedByChildof()
     *
     * @param     mixed $childof The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function filterByChildof($childof = null, $comparison = null)
    {
        if (is_array($childof)) {
            $useMinMax = false;
            if (isset($childof['min'])) {
                $this->addUsingAlias(GenrePeer::CHILDOF, $childof['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($childof['max'])) {
                $this->addUsingAlias(GenrePeer::CHILDOF, $childof['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GenrePeer::CHILDOF, $childof, $comparison);
    }

    /**
     * Filter the query by a related Genre object
     *
     * @param   Genre|PropelObjectCollection $genre The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GenreQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGenreRelatedByChildof($genre, $comparison = null)
    {
        if ($genre instanceof Genre) {
            return $this
                ->addUsingAlias(GenrePeer::CHILDOF, $genre->getId(), $comparison);
        } elseif ($genre instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GenrePeer::CHILDOF, $genre->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByGenreRelatedByChildof() only accepts arguments of type Genre or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GenreRelatedByChildof relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function joinGenreRelatedByChildof($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GenreRelatedByChildof');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GenreRelatedByChildof');
        }

        return $this;
    }

    /**
     * Use the GenreRelatedByChildof relation Genre object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \DTA\MetadataBundle\Model\Classification\GenreQuery A secondary query class using the current class as primary query
     */
    public function useGenreRelatedByChildofQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinGenreRelatedByChildof($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GenreRelatedByChildof', '\DTA\MetadataBundle\Model\Classification\GenreQuery');
    }

    /**
     * Filter the query by a related Genre object
     *
     * @param   Genre|PropelObjectCollection $genre  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GenreQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGenreRelatedById($genre, $comparison = null)
    {
        if ($genre instanceof Genre) {
            return $this
                ->addUsingAlias(GenrePeer::ID, $genre->getChildof(), $comparison);
        } elseif ($genre instanceof PropelObjectCollection) {
            return $this
                ->useGenreRelatedByIdQuery()
                ->filterByPrimaryKeys($genre->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGenreRelatedById() only accepts arguments of type Genre or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GenreRelatedById relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function joinGenreRelatedById($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GenreRelatedById');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GenreRelatedById');
        }

        return $this;
    }

    /**
     * Use the GenreRelatedById relation Genre object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \DTA\MetadataBundle\Model\Classification\GenreQuery A secondary query class using the current class as primary query
     */
    public function useGenreRelatedByIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinGenreRelatedById($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GenreRelatedById', '\DTA\MetadataBundle\Model\Classification\GenreQuery');
    }

    /**
     * Filter the query by a related GenreWork object
     *
     * @param   GenreWork|PropelObjectCollection $genreWork  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GenreQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGenreWork($genreWork, $comparison = null)
    {
        if ($genreWork instanceof GenreWork) {
            return $this
                ->addUsingAlias(GenrePeer::ID, $genreWork->getGenreId(), $comparison);
        } elseif ($genreWork instanceof PropelObjectCollection) {
            return $this
                ->useGenreWorkQuery()
                ->filterByPrimaryKeys($genreWork->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGenreWork() only accepts arguments of type GenreWork or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GenreWork relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function joinGenreWork($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GenreWork');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GenreWork');
        }

        return $this;
    }

    /**
     * Use the GenreWork relation GenreWork object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \DTA\MetadataBundle\Model\Master\GenreWorkQuery A secondary query class using the current class as primary query
     */
    public function useGenreWorkQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinGenreWork($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GenreWork', '\DTA\MetadataBundle\Model\Master\GenreWorkQuery');
    }

    /**
     * Filter the query by a related Work object
     * using the genre_work table as cross reference
     *
     * @param   Work $work the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   GenreQuery The current query, for fluid interface
     */
    public function filterByWork($work, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useGenreWorkQuery()
            ->filterByWork($work, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   Genre $genre Object to remove from the list of results
     *
     * @return GenreQuery The current query, for fluid interface
     */
    public function prune($genre = null)
    {
        if ($genre) {
            $this->addUsingAlias(GenrePeer::ID, $genre->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}