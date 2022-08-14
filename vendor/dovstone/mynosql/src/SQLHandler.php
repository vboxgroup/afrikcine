<?php
namespace DovStone\MyNoSQL;

use DovStone\MyNoSQL\Cache;
use DovStone\MyNoSQL\Session;
use DovStone\MyNoSQL\Exception;
use DovStone\MyNoSQL\SQLWhereBuilder;

class SQLHandler extends SQLWhereBuilder
{
    protected $pdo;
    protected $collection;

    public function __construct($pdo, $collection = null)
    {
        $this->pdo = $pdo;
        $this->collection = $collection;

        // $this->collection = 'users';
        // foreach ($this->_getColumns() as $col) {
        //     if( !in_array($col, ['id','uid']) ){
        //         //$sql = "ALTER TABLE `users` CHANGE `$col` `$col` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
        //         $sql = "ALTER TABLE `users` CHANGE `$col` `$col` MEDIUMTEXT NULL DEFAULT NULL;";
        //         $this->_commit($sql);
        //     }
        // }
        // dd('users');
    }
    
    public function createCollectionIfNotExists()
    {
        try {
            $sql = "CREATE TABLE 
                        IF NOT EXISTS `$this->collection`
                            ( `id` INT NOT NULL AUTO_INCREMENT, `uid` INT (8) NOT NULL, PRIMARY KEY (`id`), INDEX (`uid`))
                                ENGINE = InnoDB;";
                                    //
                                    $this->_commit($sql);
        } catch (\Exception $e) {
            $this->pdo->rollBack();
        }
        try {
            $sql = "CREATE TABLE 
                        IF NOT EXISTS `hits`
                            ( `id` INT NOT NULL AUTO_INCREMENT, `uid` INT (8) NOT NULL, `val` INT(8) NOT NULL, PRIMARY KEY (`id`), INDEX (`uid`))
                                ENGINE = InnoDB;";
                                    //
                                    $this->_commit($sql);
        } catch (\Exception $e) {
            $this->pdo->rollBack();
        }
        return $this;
    }
    
    public function thenCreateColumnsIfNotExists($documentExploded)
    {
        foreach ($documentExploded as $column => $value) {
            try {
                $sql = "ALTER TABLE `$this->collection`
                            ADD `$column` MEDIUMTEXT NULL DEFAULT NULL";
                //
                $this->_commit($sql);
            } catch (\Exception $e) {
                $this->pdo->rollBack();
            }
        }
        return $this;
    }

    public function thenInsertValue($documentExploded, $uid)
    {
        $columns = "`uid`,";
        $valuesPlaceholder = "?,";
        $values = [$uid];
        //
        foreach ($documentExploded as $column => $value) {
            $columns .= "`$column`,";
            $valuesPlaceholder .= "?,";
            $values[] = $value;
        }
        $values[] = $uid;
        //
        $columns = trim($columns, ',');
        $valuesPlaceholder = trim($valuesPlaceholder, ',');
        //
        $sql = "INSERT INTO `$this->collection` ($columns)
                    VALUES ($valuesPlaceholder)
                        ON DUPLICATE
                            KEY UPDATE uid=?";
        //
        $this->_commit($sql, $values);
        $this->__purgeCollection();
        //
        return $this;
    }

    public function delete($uid)
    {
        (new Cache())->delete($uid);
        (new Cache('fetchall'))->rmdir();
        return $this->_commit("DELETE FROM `$this->collection` WHERE uid=?", [$uid]);
    }

    public function getFindSQLData($uid)
    {
        return (object)[
            'sql' => "SELECT * FROM `$this->collection` WHERE uid=?",
            'params' => [$uid]
        ];
    }

    public function getFindBySQLData($criteria, $orderBy, $limit, $offset, $isCountBy = null, $pidsOnly = null, $pidsOnlyRandom = null)
    {
        $data = (new SQLWhereBuilder($this->pdo, $this->collection))->getWhereData($criteria, $orderBy, $limit, $offset, $isCountBy, $pidsOnly, $pidsOnlyRandom);
        return $data;
    }

    public function _fetch($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function _fetchAll($sql, $params = [])
    {
        $id = md5(serialize([$sql, $params]));
        if( $assoc = (new Cache('fetchall'))->exists($id) ) {
            return $assoc;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $assoc = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return (new Cache('fetchall'))->set($id, $assoc);

        // $stmt = $this->pdo->prepare($sql);
        // $stmt->execute($params);
        // return $assoc = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function _getColumns()
    {
        $columns = $this->_fetchAll("SHOW COLUMNS FROM `$this->collection`");
        return array_column($columns, 'Field');
    }

    public function _commit($sql, $params = [])
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $commit = $this->pdo->commit();
            return $this;
        } catch (\Exception $e) {
            //dump([$sql, $e]);
            $this->pdo->rollBack();
        }
        return $this;
    }

    private function __purgeCollection()
    {
        if($columns = $this->_getColumns()){
            foreach ($columns as $column) {
                $res = $this->_fetch("SELECT COUNT(DISTINCT `$column`) cnt FROM `$this->collection`");
                if( $res && isset($res['cnt']) && $res['cnt'] == 0 ){
                    $sql = "ALTER TABLE `$this->collection` DROP COLUMN `$column`";
                    $commit = $this->_commit($sql);
                }
            }
        }
        return $this;
    }
}