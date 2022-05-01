<?php

/**
 * Class User
 * класс для работы с базой данных людей
 */
class User{
    private $id;
    private $name;
    private $surname;
    private $birth;
    private $sex;
    private $city;
    public static $db;

    public function __construct(
        $id = NULL,
        $name = NULL,
        $surname = NULL,
        $birth = NULL,
        $sex = NULL,
        $city = NULL
    ) {
        if (isset($id) && is_numeric($id)) {
            $result = self::$db->query("SELECT * FROM `users` WHERE `id`=$id");
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->surname = $row['surname'];
            $this->birth = $row['birthdate'];
            $this->sex = $row['sex'];
            $this->city = $row['city'];
        } elseif ($this->validate($name, $surname, $birth, $sex, $city)) {
            $this->name = $name;
            $this->surname = $surname;
            $this->birth = $birth;
            $this->sex = $sex;
            $this->city = $city;
            $this->save();
        }
    }

    static public function dbConnect()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        self::$db = new mysqli('localhost', 'root', null, 'site');
        mysqli_set_charset(self::$db, 'utf8');
        return self::$db->host_info;
    }

    public function validate($name, $surname, $birth, $sex, $city)
    {
        if(
            isset($name)
            && preg_match('/^[А-Яа-яA-Za-z_-]{2,}$/', $name)
            && isset($surname)
            && preg_match('/^[А-Яа-яA-Za-z_-]{3,}$/', $surname)
            && isset($birth)
            && preg_match('/^\d{4}\-[0-1]\d\-[0-3]\d$/', $birth)
            && strtotime($birth) && isset($sex) && is_numeric($sex)
            && ($sex == 0 || $sex == 1) && isset($city)
        ){
            return true;
        }
        die('wrong data');
    }

    public function save(){
        $stmt = self::$db->prepare("INSERT INTO users (name, surname, birthdate, sex, city) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssis', $this->name, $this->surname, $this->birth, $this->sex, $this->city);
        $stmt->execute();
        $this->id = self::$db->insert_id;
    }

    public function deleteUser()
    {
        if (isset($this->id)) {
            self::$db->query("DELETE FROM `users` WHERE `id`= $this->id");
        }
    }

    public static function getAge($userId)
    {
        if (isset($userId)) {
        $result = self::$db->query("SELECT (YEAR(CURRENT_DATE) - YEAR(birthdate)) "
                . " - (DATE_FORMAT(CURRENT_DATE, '%m%d') < DATE_FORMAT(birthdate, '%m%d')) "
                . "AS age FROM users WHERE `id`=$userId");
        $row = $result->fetch_assoc();
        return $row['age'];
        }
    }

    static public function convertSex($sex)
    {
        $sex_dictionary = ['male', 'female'];
        return $sex_dictionary[$sex];
    }

    public function formatPerson($option)
    {
        $person = new StdClass();
        foreach($this as $key => $value) {
            if ($key == 'birth' && ($option == 'age' || $option == 'all')) {
                $person->age = $this->getAge($this->id);
            } elseif ($key == 'sex' && ($option == 'sex' || $option == 'all')) {
                $person->$key = $this->convertSex($this->sex);
            } else $person->$key = $value;
        }
        return $person;
    }

    public function getId()
    {
        return $this->id;
    }
}
