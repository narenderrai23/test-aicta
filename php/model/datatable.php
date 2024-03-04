<?php
require_once 'connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
class DataTablesHandler
{
    protected $db;
    private $conn;


    function __construct()
    {
        $this->db = new Connection();
        $this->conn = $this->db->getConnection();
    }

    function modifyColumns($tableName, $columns)
    {
        return array_map(function ($column) use ($tableName) {
            return $tableName . '.' . $column;
        }, $columns);
    }

    function fetchData($tableName, $columns, $countable, $count = null, $join = null)
    {
        $draw = $_POST['draw'] ?? 0;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 50;
        $searchValue = ($_POST['search']['value']) ?? '';
        $orderColumn = $_POST['order'][0]['column'] ?? 0;
        $orderDir = $_POST['order'][0]['dir'] ?? 'DESC';

        $columns = $this->modifyColumns($tableName, $columns);

        $query = $this->buildBaseQuery($tableName, $columns, $countable, $count, $join);


        $params = [];
        $query .= $this->buildWhereClause($tableName, $columns, $searchValue, $params, $count);

        if ($countable) {
            $query .= " GROUP BY $tableName.id";
        }

        $recordCount = $this->totalRecord($query, $params);

        $query .= $this->buildOrderByLimitOffset($columns, $orderColumn, $orderDir, $length, $start, $params);

        $stmt = $this->executeQuery($query, $params);

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $recordCount,
            "recordsFiltered" => $recordCount,
            "data" => isset($newData) ? $newData : $data,
            "query" => $query,
        );

        return json_encode($response);
    }

    function buildBaseQuery($tableName, $columns, $countable, $count, $join)
    {
        if ($count !== null) {
            $columns = array_merge($columns, $count);
        }
        $query = "SELECT " . implode(', ', $columns);

        $query .= $countable ? ", COUNT($count[0]) AS count" : '';

        $query .= " FROM $tableName";
        if (isset($join)) {
            foreach ($join as $table => $condition) {
                $query .= " LEFT JOIN $table ON $table.$condition[0] = $tableName.$condition[1]";
            }
        }
        return $query;
    }

    function buildWhereClause($tableName, $columns, $searchValue, &$params, $count)
    {

        $whereClause = [];

        if (!empty($searchValue)) {
            $searchConditions = [];

            if ($tableName === 'students') {
                $columns = array_unique(array_merge($columns, ['courses.course_name', 'tblbranch.name']));
            } else if ($count !== null) {
                $columns = array_unique(array_merge($columns, $count));
            }

            foreach ($columns as $column) {
                $searchConditions[] = "$column LIKE :value";
            }
            $whereClause[] = "(" . implode(' OR ', $searchConditions) . ")";
            $params[':value'] = ['value' => '%' . rtrim($searchValue) . '%', 'type' => PDO::PARAM_STR];
        }

        if (isset($_POST['data']) && is_array($_POST['data'])) {
            $whereClause = [];
            foreach ($_POST['data'] as $item) {
                $name = $item['name'];
                $searchValue = $item['value'];
                if ($name === 'created_at') {
                    if (strpos($searchValue, 'to') !== false) {
                        list($startDate, $endDate) = explode(" to ", $searchValue);
                        $whereClause[] = "DATE($tableName.$name) BETWEEN '$startDate' AND '$endDate'";
                    } else {
                        if (!empty($searchValue) && $searchValue !== 'all') {
                            $whereClause[] = "DATE($tableName.$name) = '$searchValue'";
                        }
                    }
                } else {
                    if (!empty($searchValue) && $searchValue !== 'all') {
                        $whereClause[] = "$tableName.$name = '$searchValue'";
                    }
                }
            }
        }

        if ($tableName === 'students' && $_SESSION['role'] === 'branch') {
            $branchId = $_SESSION['loggedin'];
            $whereClause[] = "$tableName.branch_id = :branchId";
            $params[':branchId'] = ['value' => $branchId, 'type' => PDO::PARAM_INT];
        }

        if (!empty($whereClause)) {
            return " WHERE " . implode(' AND ', $whereClause);
        }
        return '';
    }

    function buildOrderByLimitOffset($columns, $orderColumn, $orderDir, $length, $start, &$params)
    {
        $query = " ORDER BY " . $columns[$orderColumn] . " " . $orderDir;
        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = ['value' => $length, 'type' => PDO::PARAM_INT];
        $params[':offset'] = ['value' => $start, 'type' => PDO::PARAM_INT];

        return $query;
    }

    function totalRecord($query, $params)
    {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->rowCount();
    }

    function executeQuery($query, $params = [])
    {
        $stmt = $this->conn->prepare($query);
        foreach ($params as $paramName => $paramData) {
            $stmt->bindParam($paramName, $paramData['value'], $paramData['type']);
        }
        $stmt->execute();
        return $stmt;
    }
}
