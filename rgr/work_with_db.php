<?php
class Work_with_db {

    private $m_db_host;
    private $m_db_user;
    private $m_db_password;
    private $m_db_name;
    private $m_link;

    public function __construct($db_host, $db_user, $db_password) {
        $this->m_db_host = $db_host;
        $this->m_db_user = $db_user;
        $this->m_db_password = $db_password;
    }

    public function GetAmountOfLines($nameOfTable) {
        return array_shift(mysqli_fetch_array($this->SendSqlRequest('SELECT COUNT(1) FROM ' . $nameOfTable)));
    }

    public function WasRequestSuccessful($sql) {
        return $this->SendSqlRequest($sql) === TRUE;
    }

    public function ConnectToDb($db_name) {
        $this->m_db_name = $db_name;
        $link = mysqli_connect($this->m_db_host, $this->m_db_user, $this->m_db_password, $this->m_db_name);
        if (!$link) {
            die('<p style="color:red">'.mysqli_connect_errno().' - '.mysqli_connect_error().'</p>');
        }
        mysql_query('SET NAMES utf8');
        $this->m_link = $link;
        return $this->m_link;
    }

    public function SetDbEncoding($encoding) {
        $this->SendSqlRequest($encoding);
    }

    public function SendSqlRequest($request) {
        $result = $this->m_link->query($request);
        if (!$result) {
            die('incorrect request: ' . '&laquo' . $request . '&raquo. ' . mysql_error());
        }
        return $result;
    }

    public function GetRecords($nameOfTable, $sortingRules, $limit = NULL, $order = NULL) {
        $request = 'SELECT * FROM  ' . $nameOfTable . ' WHERE ';
        $isModificationExist = false;
        foreach($sortingRules as $field => $rule) {
            !$isModificationExist ? $isModificationExist = true : $request .= ' AND ';
            $request .= $field . ' = ' . "'" . $rule . "' ";
        }
        return $this->SendSqlRequest($request . (($order != NULL) ? (' ORDER BY ' . $order) : ('')) . (($limit != NULL) ? (' LIMIT ' . $limit) : ('')));    
    }

    public function PrintTables() {
        $result = $this->SendSqlRequest('SHOW TABLES');
        while ($row = $result->fetch_row()) {
            echo "<table><caption> {$row[0]} </caption><tr>";

            $result1 = $this->SendSqlRequest("SELECT * FROM {$row[0]}");

            if ($result1) {

                for($i = 0; $i < $this->m_link->field_count; $i++)
                {
                    $field_info = $result1->fetch_field();
                    echo "<th>{$field_info->name}</th>";
                }

                echo '</tr>';

                while ($row1 = $result1->fetch_row()) {
                    echo '<tr>';
                    foreach($row1 as $_column) {
                        echo "<td>{$_column}</td>";
                    }
                    echo '</tr>';
                }

            }
            echo '</table>';
        }
    }

    public function AddNewRecord($nameOfTable, $values) {
        $result = $this->SendSqlRequest('SHOW COLUMNS FROM ' .  $nameOfTable );
        $stringFields = '(';
        $stringValues = '(';
        foreach($values as $key => $value) {
            while ($row = $result->fetch_assoc()) {
                if ($row['Field'] == $key) {
                    $stringFields .= $key . ', '; 
                    $stringValues .= '"' . $value . '"' . ', ';
                    break;
                }
            }
        }
        $trimmedFields = rtrim($stringFields, ', ');
        $trimmedValues = rtrim($stringValues, ', ');
        $trimmedFields .= ')';
        $trimmedValues .= ')';
        $sql = "INSERT INTO $nameOfTable $trimmedFields VALUES $trimmedValues";
        if (!$this->WasRequestSuccessful($sql)) {
            echo 'Error: ' . $sql . '<br>';
        }
        return mysqli_insert_id($this->m_link);
    }

    public function UpdateRecord($nameOfTable, $sortingRules, $newValues, $limit = NULL) {
        $result = $this->SendSqlRequest('SHOW COLUMNS FROM ' .  $nameOfTable );
        $stringValues = '';
        foreach($newValues as $key => $value) {
            while ($row = $result->fetch_assoc()) {
                if ($row['Field'] == $key) {
                    $stringValues .= $key . '=' . "'" . $value . "'" . ', ';
                    break;
                }
            }
        }
        $trimmedValues = rtrim($stringValues, ', ');
        $result = $this->GetRecords($nameOfTable, $sortingRules, $limit);
        $sql = "UPDATE $nameOfTable SET $trimmedValues WHERE ";
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            if (!$this->WasRequestSuccessful($sql . 'id=' . $id )) {
                echo 'Error updating record: ';
            }
        }
    }

    public function DeleteRecord($nameOfTable, $sortingRules) {
        $result = $this->GetRecords($nameOfTable, $sortingRules, $limit);
        $sql = 'DELETE FROM  ' . $nameOfTable . ' WHERE ';
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            if (!$this->WasRequestSuccessful($sql . "id=$id" )) {
                echo 'Error deleting record: ';
            }
        }
    }

    public function Disconnect() {
        if(!mysqli_close($this->m_link))
        {
            echo('Cannot disconnect');
        }
    }
}
?>