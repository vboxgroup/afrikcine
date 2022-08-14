<?php
namespace DovStone\MyNoSQL;

use DovStone\MyNoSQL\Exception;
use DovStone\MyNoSQL\SQLBuilder;
use DovStone\MyNoSQL\SQLHandler;

class SQLWhereBuilder
{
    protected $pdo;
    protected $collection;
    protected $sql;
    protected $chainedSql;
    protected $params = [];

    public function __construct($pdo, $collection)
    {
        $this->pdo = $pdo;
        $this->collection = $collection;
    }

    public function getWhereData($criteria, $orderBy, $limit, $offset, $isCountBy = null, $pidsOnly = null, $pidsOnlyRandom = null)
    {
        $orderBy = $this->_getOrderByClause($orderBy);

        if($isCountBy){
            $this->sql = "SELECT COUNT(id) as __cnt__ FROM `$this->collection`";
        }
        elseif($pidsOnly){
            $this->sql = "SELECT uid FROM `$this->collection`";
        }
        elseif($pidsOnlyRandom){
            $this->sql = "SELECT uid FROM `$this->collection`";
            $orderBy = "ORDER BY RAND()";
        }
        else {
            $this->sql = "SELECT * FROM `$this->collection`";
        }

        //   if false then countBy()
        if( $criteria === false ){
            $this->sql = "SELECT COUNT(id) as __cnt__ FROM `$this->collection`";
        }
        //  if ! then not findAll()
        elseif( $criteria ){
            $this->sql .= " WHERE ({$this->_loopOverCriteria($criteria)})";
            $this->sql .= ($limit >= 0) ? " $orderBy LIMIT $offset, $limit" : "";
            $this->sql .= ($limit < 0 && $orderBy) ? " $orderBy" : "";
        }

        $this->sql = str_replace('()or()', ')or(', $this->sql);
        $this->sql = str_replace('and()', ')', $this->sql);

        return (object)[
            'sql' => $this->sql,
            'params' => $this->params
        ];
    }
    
    private function _getWhereClause($column, $op, $val): string
    {
        $column = ($column == 'id') ? 'uid' : $column;
        $op_o = $op;
        $op = strtolower($op);

        switch ($op) {
            case '=':
            case '==':
            case '!=':
            case '!==':
            case '>':
            case '<':
            case '>=':
            case '<=':
            case 'regexp':
            case 'not regexp':
            case 'like':
            case 'not like':
            
            /* only for null and not null */
            case 'is':
            case 'is not':

            /* path against val */
            /* array against non-array */
            case 'contains':
            case 'not contains':

                if( !is_numeric($val) && !is_string($val) && !is_null($val) ){
                    throw new Exception("Value of the condition operator \"$op_o\" must be of the type \"numeric\" or \"string\" or \"null\". \"".gettype($val)."\" given. Path:\"$column\". SQLWhereBuilder.php", 1);
                }

                if( $op === '!==' ){ $op = '!='; }
                if( $op === '==' ){ $op = '='; }

                if( in_array($op, ['contains', 'not contains']) ){

                    if($columns = (new SQLHandler($this->pdo, $this->collection))->_getColumns()){
                        $op = ($op == 'contains') ? '=' : '!=';
                        $where = "(";
                        foreach ($columns as $col) {
                            if( strpos($col, $column) !== false ){
                                $where .= "(`$col` $op ? and `$col` is not null) ".($op == '=' ? 'or' : 'and')." ";
                                $this->params[] = $val;
                            }
                        }
                        $where .= ")";
                    }
                    $where = str_replace(' or )', ')', $where);
                    $where = str_replace(' and )', ')', $where);
                }

                elseif( in_array($op, ['like', 'not like']) ){
                    $where = "(`$column` $op ?)";
                    $this->params[] = "%$val%";
                }

                elseif( in_array($op, ['is', 'is not']) ){
                    $where = "(`$column` $op $val)";
                    //$this->params[] = $val;
                }

                elseif( $this->_isValidDate($val) ){
                    $where = "(`$column` $op cast(? as datetime))";
                    $this->params[] = $val;
                }

                else {
                    $where = "`$column` $op ?";
                    $this->params[] = $val;
                }
            break;
            
            /* path against val */
            /* array against non-array */
            case 'contains >': case 'contains >=': case 'contains <': case 'contains <=' :
            case 'not contains >': case 'not contains >=': case 'not contains <': case 'not contains <=':
                
                if( !is_numeric($val) && !is_string($val) && !is_null($val) ){
                    throw new Exception("Value of the condition operator \"$op_o\" must be of the type \"numeric\" or \"string\" or \"null\". \"".gettype($val)."\" given. Path:\"$column\". SQLWhereBuilder.php", 1);
                }

                if($columns = (new SQLHandler($this->pdo, $this->collection))->_getColumns()){

                    $op = stripos($op_o, 'not contains') === false ? '=' : '!=';
                    $sign = explode('contains', $op_o);
                    $sign = trim($sign[1] ?? null);
                    if($sign !== ''){
                        $where = "(";
                        foreach ($columns as $col) {
                            if( stripos($col, $column) !== false ){
                                if($this->_isValidDate($val)){
                                    $where .= "`$col` $sign cast(? as datetime) ".($op == '=' ? 'or' : 'and')." ";
                                }
                                else {
                                    $where .= "(`$col` $sign ? and `$col` is not null) ".($op == '=' ? 'or' : 'and')." ";
                                }

                                $this->params[] = $val;
                            }
                        }
                        $where .= ")";
                    }
                }
                if( $where !== '()' ){
                    $where = str_replace(' or )', ')', $where);
                    $where = str_replace(' and )', ')', $where);
                }
                
            break;

            /* path against val */
            /* non-array against array */
            case 'in':
            case 'not in':
                
                if( !is_array($val) ){
                    throw new Exception("Value of the condition operator \"$op_o\" must be of the type \"array\". \"".gettype($val)."\ given. Path:\"$column\". SQLWhereBuilder.php", 1);
                }
                $where = "(`$column` $op (";
                $where .= "'".implode("','", $val)."'";
                $where .= "))";
            break;
                
            /* path against val */
            /* array against array */
            case 'exists in':
            case 'not exists in':

                if( !is_array($val) ){
                    throw new Exception("Value of the condition operator \"$op_o\" must be of the type \"array\". \"".gettype($val)."\ given. Path:\"$column\". SQLWhereBuilder.php", 1);
                }

                $columns = (new SQLHandler($this->pdo, $this->collection))->_getColumns();
                if($columns){
                    $op = ($op == 'exists in') ? 'in' : 'not in';
                    $where = "(";
                    foreach ($columns as $col) {
                        if( strpos($col, $column) !== false ){
                            
                            $where .= "(`$col` $op (";
                            $where .= "'".implode("','", $val)."'";
                            $where .= "))";
                            $where .= ($op == 'in') ? ' or ' : ' and ';
                        }
                    }
                    $where .= ")";
                }

                $where = str_replace(' or )', ')', $where);
                $where = str_replace(' and )', ')', $where);
            break;
            
            default:
                    throw new Exception("Condition operator \"$op\" is unknown. SQLWhereBuilder.php", 1);
                break;
        }
        return $where;
    }
    
    private function _loopOverCriteria($criteria)
    {
        $sql = '';
        $lastJoined = false;

        foreach ($criteria as $k => $cri) {
            if( is_string($cri) ){
                if( in_array($cri, ['and', 'or', 'AND', 'OR']) ){
                    $sql .= $k > 0 ? strtolower($cri) : "";
                    $lastJoined = "condition";
                }
                else {
                    if( is_array($criteria) && count($criteria) === 3 ){
                        return $this->_concatSql($criteria);
                    }
                    throw new Exception(sprintf('Malformed $criteria. Use only "and" or "or" logical. Case insensitive, "%s" given', $cri), 1);
                }
            }
            else {
                $s_q_l = isset($cri[0]) && is_array($cri[0]) ? $this->_loopOverCriteria($cri) : $this->_concatSql($cri);
                
                if(
                    stripos($s_q_l, '=') !== false
                    ||
                    stripos($s_q_l, '!') !== false
                    ||
                    stripos($s_q_l, '>') !== false
                    ||
                    stripos($s_q_l, '<') !== false
                    ||
                    stripos($s_q_l, 'regexp') !== false
                    ||
                    stripos($s_q_l, 'not') !== false
                    ||
                    stripos($s_q_l, 'like') !== false
                    ||
                    stripos($s_q_l, 'contains') !== false
                    ||
                    stripos($s_q_l, 'in') !== false
                    ||
                    stripos($s_q_l, 'is') !== false
                    ||
                    stripos($s_q_l, 'exists') !== false
                ){
                    $sql .= $lastJoined == "array" ? 'and' : '';
                    $sql .= '('. $s_q_l . ')';
                    $lastJoined = "array";
                    $sql = str_ireplace('oror', '', $sql);
                    $sql = str_ireplace('andand', '', $sql);
                    $sql = str_ireplace('and(and', 'and(', $sql);
                    $sql = str_ireplace('(or(', 'or((', $sql);
                    $sql = str_ireplace('(and(', 'and((', $sql);
                    $sql = str_ireplace('andor', 'and', $sql);
                    $sql = str_ireplace('orand', 'or', $sql);
                    $sql = str_ireplace('and(or', 'and(', $sql);
                    $sql = str_ireplace(')or)', '))', $sql);
                }
            }
        }
        return empty($sql) ? null : $sql;
    }

    private function _concatSql($criteria)
    {
        if( is_countable($criteria) ){
            if( count($criteria) !== 3 ){
                throw new Exception(sprintf('Malformed $criteria. Exactly 3 parameters expected, %s given', count($criteria)), 1);
            }
            $column = $criteria[0];
            $op = $criteria[1];
            $val = $criteria[2];
            return $this->_getWhereClause($column, $op, $val);
        }
        throw new Exception('Malformed $criteria', 1);
    }

    private function _getOrderByClause($orderBy)
    {
        if( $orderBy ){
            $str = ' ORDER BY ';
            if(!isset($orderBy[0])){
                $orderBy = [$orderBy];
            }
            foreach ($orderBy as $data) {
                foreach($data as $col => $val){
                    if( in_array(strtolower($val), ['asc', 'desc']) ){
                        $val = strtoupper($val);
                        //$str .= "cast(`$col` as datetime) $val, ";
                        $str .= strpos($col, 'cast') !== false && strpos($col, 'as') !== false ? "$col $val, " : "`$col` $val, ";
                    }
                }
            }
            $str = trim($str, ', ');
            return $str;
        }
        return '';
    }

    private function _isValidDate($date, $format='Y-m-d'): bool
    {
        if(stripos($date, ':') === false){ return false; }
        try { new \DateTime($date); return true; } catch (\Throwable $th) { return false; }
        //$d = \DateTime::createFromFormat($format, $date);
        //return $d && $d->format($format) === $date;
    }
}