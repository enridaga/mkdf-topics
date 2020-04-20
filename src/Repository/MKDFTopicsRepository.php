<?php

namespace MKDF\Topics\Repository;

use Zend\Db\Adapter\Adapter;
use MKDF\Core\Entity\Bucket;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;


class MKDFTopicsRepository implements MKDFTopicsRepositoryInterface
{
    private $_config;
    private $_adapter;
    private $_queries;

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_adapter = new Adapter([
            'driver'   => 'Pdo_Mysql',
            'database' => $this->_config['db']['dbname'],
            'username' => $this->_config['db']['user'],
            'password' => $this->_config['db']['password'],
            'host'     => $this->_config['db']['host'],
            'port'     => $this->_config['db']['port']
        ]);
        $this->buildQueries();
    }
    

    private function fp($param) {
        return $this->_adapter->driver->formatParameterName($param);
    }
    private function qi($param) {
        return $this->_adapter->platform->quoteIdentifier($param);
    }
    private function buildQueries(){
        $this->_queries = [
            'isReady'           => 'SELECT ID FROM collection LIMIT 1',
            //'allCollections'       => 'SELECT id,title,description,user_id,date_created,date_modified FROM collection ORDER BY date_created DESC',
            'allCollections'        => 'SELECT id,title,description,user_id,date_created,date_modified, COALESCE(x.cnt,0) AS dataset_count '.
                'FROM collection '.
                'LEFT OUTER JOIN (SELECT collection_id, count(collection_id) cnt FROM collection__dataset GROUP BY collection_id) x ON collection.id = x.collection_id '.
                'ORDER BY date_created DESC ',
            'oneCollection'        => 'SELECT id,title,description,user_id,date_created,date_modified FROM collection WHERE id = ' . $this->fp('id'),
            'collectionCount'       => 'SELECT COUNT(id) AS count FROM collection',
            'insertCollection'      => 'INSERT INTO collection (title, description, user_id) VALUES (' .   $this->fp('title') . ', ' . $this->fp('description') . ', ' . $this->fp('user_id') .')',
            'updateCollection'      => 'UPDATE collection SET ' . 
                $this->qi('title') . '=' . $this->fp('title') . ', ' . 
                $this->qi('description') .'='. $this->fp('description') . ', ' .
                $this->qi('date_modified') .'='. 'CURRENT_TIMESTAMP WHERE id = ' . $this->fp('id'),
            'deleteCollection'      => 'DELETE FROM collection WHERE ' . $this->qi('id') . ' = ' . $this->fp('id'),
            'addToCollection' => 'INSERT IGNORE INTO collection__dataset (collection_id, dataset_id) VALUES (' . $this->fp('collection_id') . ',' . $this->fp('dataset_id') .  ')',
            'datasetsInCollection' => 'SELECT dataset.id,dataset.title,dataset.uuid,dataset.description FROM dataset INNER JOIN  collection__dataset ON  collection__dataset.collection_id = ' . $this->fp('collection_id') . ' AND collection__dataset.dataset_id = dataset.id',
            'datasetsNotInCollection' => 'SELECT dataset.id,dataset.title,dataset.uuid,dataset.description FROM dataset WHERE dataset.id NOT IN (SELECT dataset_id FROM collection__dataset WHERE collection_id = ' . $this->fp('collection_id') . ')',
            'datasetsVisibleInCollection' => 'SELECT d.id, d.title, d.uuid, d.description '.
                ' FROM dataset d INNER JOIN  collection__dataset cd ON  cd.collection_id = ' . $this->fp('collection_id').
                ' AND cd.dataset_id = d.id '.
                'INNER JOIN dataset_permission dp ON '.
                    'd.id = dp.dataset_id AND '.
                        '('.
                            '(dp.role_id = '.$this->fp('login_status').' AND dp.v = 1)'.
                            ' OR '.
                            '(d.user_id = '.$this->fp('user_id').' AND dp.role_id = '.$this->fp('logged_in_identifier').')'.
                            ' OR '.
                            '(dp.role_id = '.$this->fp('user_id').' AND dp.v = 1)'.
                        ')',
            'datasetsVisibleNotInCollection' => 'SELECT DISTINCT d.id, d.title, d.uuid, d.description '.
                ' FROM dataset d '.
                ' INNER JOIN dataset_permission dp ON '.
                    'd.id = dp.dataset_id AND '.
                    '('.
                        '(dp.role_id = '.$this->fp('login_status').' AND dp.v = 1)'.
                        ' OR '.
                        '(d.user_id = '.$this->fp('user_id').' AND dp.role_id = '.$this->fp('logged_in_identifier').')'.
                        ' OR '.
                        '(dp.role_id = '.$this->fp('user_id').' AND dp.v = 1)'.
                    ')'.
                ' WHERE d.id NOT IN '.
                    ' (SELECT dataset_id FROM collection__dataset '.
                    ' WHERE collection_id = ' . $this->fp('collection_id') . ') ',
            'removeFromCollection' => 'DELETE FROM collection__dataset WHERE collection_id = ' . $this->fp('collection_id') . ' AND dataset_id =' . $this->fp('dataset_id'),
            'datasetCollections' => 'SELECT c.id, c.title, c.description FROM collection c, collection__dataset cd '.
                    ' WHERE c.id = cd.collection_id AND cd.dataset_id = '.$this->fp('dataset_id'),

        ];
    }

    private function addQueryLimit($query, $limit) {
        return $query . ' LIMIT ' . $limit;
    }

    private function getQuery($query){
        return $this->_queries[$query];
    }
    
    /**
     * @return array returns an array of Bucket
     */
    public function findAllCollections($limit = 0){
        $collections = [];
        $query = $this->getQuery('allCollections');
        if ($limit > 0) {
            $query = $this->addQueryLimit($query, $limit);
        }
        $statement = $this->_adapter->createStatement($query);
        $result    = $statement->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            foreach ($resultSet as $row) {
                $b = new Bucket();
                $b->setProperties($row);
                array_push($collections, $b);
            }
            return $collections;
        }
        return [];
    }
    
    /**
     * @param int $id collection id
     * @return Bucket
     */
    public function findCollection($id){
        $statement = $this->_adapter->createStatement($this->getQuery('oneCollection'));
        $result    = $statement->execute(['id'=>$id]);
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            if ($result->count() > 0) {
                $b = new Bucket();
                $b->setProperties($result->current());
                return $b;
            }
        }
        return null;
    }

    public function getCollectionCount() {
        $parameters = [];
        $statement = $this->_adapter->createStatement($this->getQuery('collectionCount'));
        $result    = $statement->execute($parameters);
        $collectionCount = 0;
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $currentResult = $result->current();
            $collectionCount = (int)$currentResult['count'];
        }
        return $collectionCount;
    }
    
    /**
     * @return array returns an array of Bucket
     */
    public function insertCollection($data){
        $statement = $this->_adapter->createStatement($this->getQuery('insertCollection'));
        $statement->execute($data);
        $id = $this->_adapter->getDriver()->getLastGeneratedValue();
        return $id;
    }
    
    /**
     * @return 
     */
    public function updateCollection($id, $title, $description){
        $statement = $this->_adapter->createStatement($this->getQuery('updateCollection'));
        $statement->execute(['id'=>$id,'title'=>$title,'description'=>$description]);
        // FIXME Need to decide whether return anything useful
        return true;
    }
    
    /**
     * @return 
     */
    public function deleteCollection($id){
        $statement = $this->_adapter->createStatement($this->getQuery('deleteCollection'));
        $outcome = $statement->execute(['id'=>$id]);
        // FIXME Need to decide whether return anything useful
        return true;
    }
    
    public function datasetsInCollection($id, $userId = -1){
        if ($userId > 0) {
            $loginStatus = -1; //signifies logged in, in roles table
        }
        else {
            $loginStatus = -2; //as per anonymous role in roles table
            $userId = -2; //if not logged in, use -2 (anonymous) also as the user to query against the role permissions
        }
        $parameters = [
            'collection_id' => $id,
            'login_status'  => $loginStatus,
            'user_id'       => $userId,
            'logged_in_identifier' => -1
        ];

        $datasets = [];
        $statement = $this->_adapter->createStatement($this->getQuery('datasetsVisibleInCollection'));
        $result    = $statement->execute($parameters);
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            foreach ($resultSet as $row) {
                $b = new Bucket();
                $b->setProperties($row);
                array_push($datasets, $b);
            }
            return $datasets;
        }
        return [];
    }
    
    public function datasetsNotInCollection($id, $userId = -1){
        if ($userId > 0) {
            $loginStatus = -1; //signifies logged in, in roles table
        }
        else {
            $loginStatus = -2; //as per anonymous role in roles table
            $userId = -2; //if not logged in, use -2 (anonymous) also as the user to query against the role permissions
        }
        $parameters = [
            'collection_id' => $id,
            'login_status'  => $loginStatus,
            'user_id'       => $userId,
            'logged_in_identifier' => -1
        ];

        $datasets = [];
        $statement = $this->_adapter->createStatement($this->getQuery('datasetsVisibleNotInCollection'));
        $result    = $statement->execute($parameters);
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            foreach ($resultSet as $row) {
                $b = new Bucket();
                $b->setProperties($row);
                array_push($datasets, $b);
            }
            return $datasets;
        }
        return [];
    }

    public function datasetCollections($datasetId) {
        $collections = [];
        $parameters = [
            'dataset_id' => $datasetId,
        ];
        $statement = $this->_adapter->createStatement($this->getQuery('datasetCollections'));
        $result    = $statement->execute($parameters);
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            foreach ($resultSet as $row) {
                array_push($collections, $row);
            }
            return $collections;
        }
        return [];
    }
    
    /**
     * @return 
     */
    public function addToCollection($id, $datasets){
        try{
            $connection = $this->_adapter->getDriver()->getConnection();
            $connection->beginTransaction();
            $statement = $this->_adapter->createStatement($this->getQuery('addToCollection'));
            foreach($datasets as $dataset){
                $statement->execute(['dataset_id'=>$dataset, 'collection_id'=>$id]);
            }
            $connection->commit();
        }catch(\PDOException $e){
            $connection->rollback();
            throw $e;
        }
        // FIXME Need to decide whether return anything useful
        return true;
    }
    
    public function removeFromCollection($id, $dataset){
        $statement = $this->_adapter->createStatement($this->getQuery('removeFromCollection'));
        $statement->execute(['dataset_id'=>$dataset, 'collection_id'=>$id]);
        // FIXME Need to decide whether return anything useful
        return true;
    }
    
    public function init(){
        try {
            $statement = $this->_adapter->createStatement($this->getQuery('isReady'));
            $result    = $statement->execute();
            return false;
        } catch (\Exception $e) {
            // XXX Maybe raise a warning here?
        }
        $sql = file_get_contents(dirname(__FILE__) . '/../../sql/setup.sql');
        $this->_adapter->getDriver()->getConnection()->execute($sql);
        return true;
    }
}
