<?php
namespace DovStone\MyNoSQL;

use DovStone\MyNoSQL\DocumentPloder;
use DovStone\MyNoSQL\QueryBuilder;
use DovStone\MyNoSQL\SQLHandler;

class QueryBuilder
{
    protected $pdo;
    protected $collection;
    protected $criteria;
    protected $orderBy;
    protected $limit;
    protected $offset;
    protected $offsetQuery;
    protected $_getMethod;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function collection($collection)
    {
        $this->collection = $this->getValidCollectionName($collection);
        return $this;
    }

    public function getPDO()
    {
        return $this->pdo;
    }
    
    public function insert($document, $uid = null)
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        $documentExploded = (new DocumentPloder())->explode($document);
        $SQLHandler = new SQLHandler($this->pdo, $this->collection);
        if( $documentExploded ){
            $uid = $uid ?? substr(crc32(uniqid()), 0, 8);
            $SQLHandler
                ->createCollectionIfNotExists()
                    ->thenCreateColumnsIfNotExists($documentExploded)
                        ->thenInsertValue($documentExploded, $uid);
            return $this->find($uid)->fetch();
        }
        return false;
    }
    
    public function update($uid, $newDocument)
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->delete($uid);
        return $this->insert($newDocument, $uid);
    }
    
    public function delete($uid)
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        (new SQLHandler($this->pdo, $this->collection))->delete($uid);
        return true;
    }
    
    public function deleteBy(array $criteria = [])
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->_getMethod = 'getFindBySQLData';
        $this->_findType = 'findBy';
        //
        $this->criteria = $criteria;
        $this->limit = 1;
        $this->offset = 0;
        //
        $document = $this->fetch();
        if(isset($document['id'])){
            $this->delete($document['id']);
        }
        //
        return $this;
    }

    public function find($uid)
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->_getMethod = 'getFindSQLData';
        $this->_findType = 'findOneBy';
        //
        $this->limit = 1;
        $this->offset = 0;
        //
        $this->uid = $uid;
        //
        unset($this->isCountBy);
        unset($this->uidsOnly);
        unset($this->definedLimit);
        unset($this->definedOrderBy);
        //
        return $this;
    }

    public function findIDs(array $criteria = [])
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->_getMethod = 'getFindBySQLData';
        $this->_findType = 'findBy';
        //
        $this->criteria = $criteria;
        $this->limit = -1;
        $this->offset = 0;
        $this->uidsOnly = true;
        //
        unset($this->isCountBy);
        unset($this->uid);
        unset($this->definedLimit);
        unset($this->definedOrderBy);
        //
        return $this;
    }
    
    public function findBy(array $criteria = [], array $orderBy = ['createdAt' => 'desc'], int $limit = 15, int $offset = null, $offsetQuery = 'page')
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->_getMethod = 'getFindBySQLData';
        $this->_findType = 'findBy';
        //
        $this->criteria = $criteria;
        $this->orderBy = $orderBy;
        $this->limit = $limit;
        $this->offset = in_array($this->limit, [-1, 1]) ? 0 : ($offset ?? $this->getOffset($limit, $offsetQuery));
        $this->offsetQuery = $offsetQuery;
        //
        unset($this->isCountBy);
        unset($this->uidsOnly);
        unset($this->uid);
        //
        return $this;
    }
    
    public function findOneBy(array $criteria = [])
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->_getMethod = 'getFindBySQLData';
        $this->_findType = 'findOneBy';
        $this->limit = 1;
        $this->offset = 0;
        //
        $this->criteria = $criteria;
        //
        unset($this->isCountBy);
        unset($this->uidsOnly);
        unset($this->uid);
        unset($this->definedLimit);
        unset($this->definedOrderBy);
        //
        return $this;
    }
    
    public function findAllBy(array $criteria = [], array $orderBy = ['createdAt' => 'desc'])
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->_getMethod = 'getFindBySQLData';
        $this->_findType = 'findBy';
        //
        $this->criteria = $criteria;
        $this->limit = -1;
        $this->offset = 0;
        //
        unset($this->isCountBy);
        unset($this->uidsOnly);
        unset($this->uid);
        unset($this->definedLimit);
        unset($this->definedOrderBy);
        //
        return $this;
    }
    
    public function findAll(array $orderBy = ['createdAt' => 'desc'])
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->_getMethod = 'getFindBySQLData';
        $this->_findType = 'findBy';
        //
        $this->criteria = null;
        $this->limit = -1;
        $this->offset = 0;
        //
        unset($this->isCountBy);
        unset($this->uidsOnly);
        unset($this->uid);
        unset($this->definedLimit);
        unset($this->definedOrderBy);
        //
        return $this;
    }
    
    public function count()
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $sql = "SELECT COUNT(id) as __cnt__ FROM `$this->collection`";
        $row = (new SQLHandler($this->pdo, $this->collection))->_fetch($sql);
        //
        return (int) $row['__cnt__'];
    }
    
    public function countBy(array $criteria = [])
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->_getMethod = 'getFindBySQLData';
        $this->_findType = 'findBy';
        //
        $this->isCountBy = true;
        //
        $this->criteria = $criteria;
        $this->limit = -1;
        $this->offset = 0;
        //
        unset($this->uidsOnly);
        unset($this->uid);
        unset($this->definedLimit);
        unset($this->definedOrderBy);
        //
        return $this;
    }

    public function orderBy(array $orderBy)
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->definedOrderBy = $orderBy;
        //
        return $this;
    }

    public function offset(int $offset = null, string $pageQuery = 'page')
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->definedOffset = $offset ?? $this->getOffset($limit, $pageQuery);
        $this->pageQuery = $pageQuery;
        //
        return $this;
    }

    public function limit(int $limit)
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $this->definedLimit = $limit;
        //
        return $this;
    }
    
    public function getOffset(int $limit, string $pageQuery = 'page')
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        $page = $_GET[$pageQuery] ?? 1;
        $page = $page <= 0 ? 1 : $page;
        $offset = $page * $limit - $limit;
        $this->limit = $limit;
        $this->pageQuery = $pageQuery;
        return $offset;
    }

    public function getSQLData()
    {
        if( !isset($this->_getMethod) ){
            throw new Exception('No collection was defined.');
        }
        else {
            /*if( !isset($this->isCountBy) ){
                $this->limit = $this->definedLimit ?? $this->limit ?? 15;
                $this->offset = $this->definedOffset ?? in_array($this->limit, [-1,1]) ? 0 : $this->getOffset($this->limit);
                $this->orderBy = $this->definedOrderBy ?? $this->orderBy ?? [];
            }*/
            //
            (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
            //
            switch($this->_getMethod){
                case 'getFindSQLData';
                    return (new SQLHandler($this->pdo, $this->collection))->getFindSQLData(
                        $this->uid
                    );
                break;
                case 'getFindBySQLData';
                    return (new SQLHandler($this->pdo, $this->collection))->getFindBySQLData(
                        $this->criteria ?? [],
                        $this->definedOrderBy ?? $this->orderBy ?? [],
                        $this->definedLimit ?? $this->limit ?? 15,
                        $this->definedOffset ?? $this->definedOffset ?? $this->getOffset($this->definedLimit ?? $this->limit),
                        $this->isCountBy ?? null,
                        $this->uidsOnly ?? null
                    );
                break;
                default: return null; break;
            }
        }
    }

    public function getSQL()
    {
        $SQLData = $this->getSQLData();
        if( !$SQLData ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        return $SQLData->sql;
    }

    public function getSQLParams()
    {
        $SQLData = $this->getSQLData();
        if( !$SQLData ){
            throw new Exception('No collection was defined.');
        }
        //
        (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
        //
        return $SQLData->params;
    }

    public function fetch()
    {
        if( !isset($this->collection) ){
            throw new Exception('No collection was defined.');
        }
        try {
            //
            (new SQLHandler($this->pdo, $this->collection))->createCollectionIfNotExists();
            //
            $SQLData = $this->getSQLData();
            $rows = (new SQLHandler($this->pdo, $this->collection))->_fetchAll($SQLData->sql, $SQLData->params);

            if(array_column($rows, '__cnt__') && isset(array_column($rows, '__cnt__')[0])){
                return (int) array_column($rows, '__cnt__')[0];
            }
            if($rows){
                $documents = [];
                
                if(!isset($this->documentPloder)){$this->documentPloder = (new DocumentPloder());}
                if(!isset($this->cache)){$this->cache = (new Cache());}

                foreach ($rows as $i => $row) {
                    $uid = $rows[$i]['uid'];
                    if($this->cache->exists($uid)){
                        $documents[] = $this->cache->get($uid);
                        unset($rows[$i]);
                    }
                    else {
                        foreach ($row as $column => $val) {
                            if(!$val || $val == 'null'){ unset($rows[$i][$column]);}
                        }
                    }
                }
                if($rows){
                    foreach ($rows as $row) {
                        if($document = $this->documentPloder->implode($row)){
                            $documents[] = $document;
                            $this->cache->set($row['uid'], $document);
                        }
                    }
                }

                if($this->_findType == 'findOneBy' || (isset($this->limit) && $this->limit == 1) ){
                    return isset($documents[0]) ? $documents[0] : null;
                }

                if(isset($this->uidsOnly)){
                    return array_column($documents, 'id');
                }

                return $documents ?? [];
            }

        } catch (\Exception $e) {
            new Exception($e->getMessage());
            return isset($this->isCountBy) ? 0 : [];
        }
        //
        return isset($this->isCountBy) ? 0 : [];
    }

    public function commit($sql, array $params = null)
    {
        return (new SQLHandler($this->pdo, $this->collection))->_commit($sql, $params);
    }

    private function getValidCollectionName($collection)
    {
        return strtolower($collection);
    }
}