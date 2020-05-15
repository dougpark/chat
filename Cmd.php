<?php

class Cmd
{
    private $host = 'localhost';
    private $user = 'phpzag_demo';
    private $password = "123";
    private $database = "phpzag_demo";
    private $chatTable = 'chat';
    private $chatUsersTable = 'chat_users';
    private $chatLoginDetailsTable = 'chat_login_details';
    private $dbConnect = false;

    //private $host = '127.0.0.1';
    private $db = 'phpzag_demo';
    //private $user = 'root';
    private $pass = '123';
    private $port = "3306";
    private $charset = 'utf8';
    private $pdo = '';

    private $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public function __construct()
    {

        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset};port={$this->port}";
        try {
            $this->pdo = new \PDO($dsn, $this->user, $this->pass, $this->options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }

        //$this->getUserDetailsPDO(1);
    }

    // get data on one user = userid
    public function getUserDetailsPDO($userid)
    {
        // $sqlQuery = "
        //     SELECT * FROM " . $this->chatUsersTable . "
        //     WHERE userid = '$userid'";
        // return $this->getData($sqlQuery);

        // select a particular user by id
        $sql = "SELECT *
                FROM {$this->chatUsersTable}
                WHERE userid=:userid
                AND username=:username";
        $data = [
            'userid' => $userid,
            'username' => 'Rose',
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        //$user = $stmt->fetch();
        $result = $stmt->fetchAll();
        // go through all the returned rows
        foreach ($result as $row) {
            $username = $row['username'];
            error_log("username= $username {$row['username']}");
            // or go through all the fields in the returned row
            // foreach ($row as $field => $value) {
            //     error_log("$field= $value");
            // }

        }
    }
}
