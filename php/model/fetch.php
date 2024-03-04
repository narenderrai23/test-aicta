<?php
require_once('connection.php');

class Fetch
{
    protected $db;

    public function __construct()
    {
        $this->db = new Connection();
    }


    public function fetch($table, $where = null)
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) FROM $table";
        $whereClause = [];
        $params = [];

        if (!is_null($where)) {
            $whereClause[] = $where;
        }

        if ($_SESSION['role'] === 'branch' && $table === 'students') {
            $whereClause[] = "branch_id = :branchId";
            $params[':branchId'] = ['value' => $_SESSION['loggedin'], 'type' => PDO::PARAM_INT];
        }

        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }

        $stmt = $conn->prepare($sql);

        foreach ($params as $param => $config) {
            $stmt->bindValue($param, $config['value'], $config['type']);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }


    public function graph($table, $column, $where = null)
    {
        $conn = $this->db->getConnection();
        $array = [
            'today' => date('Y-m-d'),
            'week' => date('Y-m-d', strtotime('last Sunday')),
            'month' => date('Y-m-d', strtotime('-1 month')),
            'year' => date('Y-m-d', strtotime('-1 year')),
        ];
        $graphData = [];

        

        foreach ($array as $key => $value) {
            $sql = "SELECT COUNT(*) FROM $table WHERE $column BETWEEN :minDate AND :maxDate";

            $whereClause = [];
            $params = [
                ':minDate' => ['value' => $value, 'type' => PDO::PARAM_STR],
                ':maxDate' => ['value' => date('Y-m-d'), 'type' => PDO::PARAM_STR]
            ];

            if (!is_null($where)) {
                $whereClause[] = $where;
            }

            if (!empty($whereClause)) {
                $sql .= " AND " . implode(" AND ", $whereClause);
            }

            $stmt = $conn->prepare($sql);

            foreach ($params as $param => $config) {
                $stmt->bindValue($param, $config['value'], $config['type']);
            }

            $stmt->execute();
            $graphData[$key] = $stmt->fetchColumn();
        }

        return $graphData;
    }







    public function topBranch($table, $column)
    {
        $conn = $this->db->getConnection();

        // Common part of both queries
        $common_sql = "SELECT tblbranch.*, cities.state AS state_id, states.state_name, subquery.id, subquery.count
       FROM tblbranch
    JOIN (
        SELECT $table.$column AS id, COUNT(*) AS count
                FROM $table
                GROUP BY $table.$column
                LIMIT 10
    ) AS subquery
    ON tblbranch.id = subquery.id
    JOIN cities ON tblbranch.city_id = cities.id 
    JOIN states ON cities.state = states.id";

        // All-time query
        $all_sql = "$common_sql
    ORDER BY subquery.count DESC";

        $stmt = $conn->prepare($all_sql);
        $stmt->execute();
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Current month query
        $month_sql = "$common_sql
        WHERE MONTH(tblbranch.created_at) = MONTH(CURRENT_DATE())
        AND YEAR(tblbranch.created_at) = YEAR(CURRENT_DATE())
        ORDER BY subquery.count DESC";

        $stmt = $conn->prepare($month_sql);
        $stmt->execute();
        $month = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'all' => $all,
            'month' => $month,
        ];
    }

    public function recentAdded($table)
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT $table.*,
        states.state_name
        FROM $table
        JOIN cities ON tblbranch.city_id = cities.id 
        JOIN states ON cities.state = states.id 
        ORDER BY created_at DESC
        LIMIT 10;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function oldAdded($table)
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT $table.*,
        states.state_name
        FROM $table
        JOIN cities ON tblbranch.city_id = cities.id 
        JOIN states ON cities.state = states.id 
        ORDER BY created_at ASC
        LIMIT 10;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }



    public function fetchDataForLast12Months($table, $column)
    {
        $conn = $this->db->getConnection();
        $year = date('Y');
        $result = [];
        $currentMonth = date('m');

        for ($i = 0; $i < $currentMonth; $i++) {
            $startDate = new DateTime("$year-01-01 +$i months");

            $endDate = new DateTime($startDate->format('Y-m-d'));
            $endDate->add(new DateInterval('P1M'));
            $endDate->sub(new DateInterval('P1D'));

            $formattedStartDate = $startDate->format('Y-m-d');
            $formattedEndDate = $endDate->format('Y-m-d');

            $sql = "SELECT COUNT(*) FROM $table WHERE $column BETWEEN :startDate AND :endDate";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':startDate', $formattedStartDate);
            $stmt->bindParam(':endDate', $formattedEndDate);
            $stmt->execute();
            $result[$i] = $stmt->fetchColumn();
        }

        return $result;
    }
}