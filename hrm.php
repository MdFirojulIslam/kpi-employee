<?php

require_once(LIB_PATH . DS . 'database.php');

class HRM extends DatabaseObject
{
    public static function fetch_user_id()
    {
        global $database;
        $sql = "SELECT id, fname, lname FROM user WHERE id != 1 ORDER BY fname ASC";
        $results = $database->query($sql);

        $user_details = [];
        foreach ($results as $row) {
            $full_name = implode(' ', [$row['fname'], $row['lname']]);
            $user_details[] = [
                'id' => $row['id'],
                'full_name' => $full_name
            ];
        }
        return $user_details;
    }

    public static function hr_kpi_teamsetable()
    {
        global $database;
        $sql = "SELECT t1.id AS kpi_id, t1.teamleadID, t1.memberID, t1.category, t1.status, t1.from_date, t1.to_date, 
                t2.fname AS teamlead_fname, t2.lname AS teamlead_lname, 
                t3.fname AS member_fname, t3.lname AS member_lname
                FROM hr_kpi_teamset AS t1
                LEFT JOIN user AS t2 ON t1.teamleadID = t2.id
                LEFT JOIN user AS t3 ON t1.memberID = t3.id";

        $results = $database->query($sql);

        $user_info = [];
        foreach ($results as $data) {
            $teamleaderName = implode(' ', [$data['teamlead_fname'] ?? '', $data['teamlead_lname'] ?? '']);
            $memberName = implode(' ', [$data['member_fname'] ?? '', $data['member_lname'] ?? '']);

            $user_info[] = [
                'id' => $data['kpi_id'],
                'teamleader' => $teamleaderName,
                'member' => $memberName,
                'category' => $data['category'],
                'status' => $data['status'],
                'from_date' => $data['from_date'],
                'to_date' => $data['to_date']
            ];
        }
        return $user_info;
    }

    public static function countGrade($mark = NULL)
    {
        if ($mark <= 100 && $mark >= 90) {
            echo "A+";
        } elseif ($mark < 90 && $mark >= 80) {
            echo "A";
        }  elseif ($mark < 80 && $mark >= 70) {
            echo "B";
        }elseif ($mark < 70 && $mark >= 60) {
            echo "C";
        }
        else {
            echo "Failed";
        }
    }
}
