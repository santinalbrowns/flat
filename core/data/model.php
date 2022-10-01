<?php

namespace core\data;

use Exception;
use PDO;

abstract class Model
{
    protected $table;

    protected $primary_key = 'id';

    private PDO $db;

    public string $id;

    public function __construct()
    {
        if (!$this->table) {

            $table = explode('\\', get_class($this));

            $this->table = strtolower(end($table));
        }

        try {
            $dsn = isset($_ENV['DSN']) ? $_ENV['DSN'] : '';
            $user = isset($_ENV['DB_USERNAME']) ? $_ENV['DB_USERNAME'] : '';
            $password = isset($_ENV['BD_PASSWORD']) ? $_ENV['BD_PASSWORD'] : '';

            $this->db = new PDO($dsn, $user, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (Exception $e) {
            echo $e->getMessage();

            exit;
        }
    }

    public function insert($data)
    {
        $data = is_object($data) ? (array)$data : $data;

        $attributes = [];

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $attributes[$key] = $value;
            }
        }

        if (!empty($attributes)) {

            $attribute = array_keys($attributes);

            $placeholder = implode(', ', array_map(fn($attr) => ":$attr", $attribute));

            $field = implode(', ', array_map(fn($attr) => "`$attr`", $attribute));


            $sql = "INSERT INTO $this->table($field) VALUES($placeholder)";

            $statement = $this->db->prepare($sql);

            foreach ($attributes as $key => $value) {
                $statement->bindValue(":$key", $value);
            }

            if ($statement->execute()) {
                return $this->findOne([$this->primary_key => $this->db->lastInsertId()]);
            }
        }

        throw new Exception('Cannot insert null object');

    //return false;
    }

    public function find(array $args = array()): array
    {
        $args = is_object($args) ? (array)$args : $args;

        $attributes = [];

        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $attributes[$key] = $value;
            }
        }

        if (!empty($attributes)) {

            $attribute = array_keys($attributes);

            $placeholder = implode(' AND ', array_map(fn($attr) => "$attr = :$attr", $attribute));

            $sql = "SELECT * FROM $this->table WHERE $placeholder";

            $statement = $this->db->prepare($sql);

            foreach ($attributes as $key => $value) {
                $statement->bindValue(":$key", $value);
            }

            if ($statement->execute()) {

                $attributes = $statement->fetchAll(PDO::FETCH_OBJ);

                return $attributes;
            }

        }
        else {
            $sql = "SELECT * FROM `{$this->table}`";

            $statement = $this->db->prepare($sql);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_OBJ);
        }

        return [];
    }

    public function findOne(array $args = []): object|bool
    {
        $sql = null;
        $attributes = [];

        if (count($args)) {
            $args = is_object($args) ? (array)$args : $args;

            foreach ($args as $key => $value) {
                if (property_exists($this, $key)) {
                    $attributes[$key] = $value;
                }
            }

            $attribute = array_keys($attributes);

            $placeholder = implode(' AND ', array_map(fn($attr) => "$attr = :$attr", $attribute));

            $sql = "SELECT * FROM `{$this->table}` WHERE $placeholder LIMIT 1";

            $statement = $this->db->prepare($sql);

            foreach ($attributes as $key => $value) {
                $statement->bindValue(":$key", $value);
            }

            $statement->execute();

            $attributes = $statement->fetch(PDO::FETCH_OBJ);

            return $attributes;
        }
        else {
            $sql = "SELECT * FROM `{$this->table}` LIMIT 1";

            $statement = $this->db->prepare($sql);

            $statement->execute();

            $attributes = $statement->fetch(PDO::FETCH_OBJ);

            return $attributes;
        }
    }

    public function update(array $args, array $data): array |bool
    {
        $args = is_object($args) ? (array)$args : $args;

        $attributes = [];
        $data_attributes = [];

        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $attributes[$key] = $value;
            }
        }


        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $data_attributes[$key] = $value;
            }
        }

        if (!empty($attributes) && !empty($data_attributes)) {

            $attribute = array_keys($attributes);
            $data_attribute = array_keys($data_attributes);

            $placeholder = implode(' AND ', array_map(fn($attr) => "$attr = :$attr", $attribute));

            $fields = implode(' AND ', array_map(fn($fld) => "$fld = :$fld", $data_attribute));

            $sql = "UPDATE $this->table SET $fields WHERE $placeholder";

            $statement = $this->db->prepare($sql);

            foreach ($attributes as $key => $value) {
                $statement->bindValue(":$key", $value);
            }

            foreach ($data_attributes as $key => $value) {
                $statement->bindValue(":$key", $value);
            }

            if ($statement->execute() && $statement->rowCount()) {
                return $this->findOne(['id' => $this->db->lastInsertId()]);
            }

        }

        return [];
    }


    public function delete(array $args = array()): bool
    {
        $args = is_object($args) ? (array)$args : $args;

        $attributes = [];

        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $attributes[$key] = $value;
            }
        }

        if (!empty($attributes)) {

            $attribute = array_keys($attributes);

            $placeholder = implode(' AND ', array_map(fn($attr) => "$attr = :$attr", $attribute));

            $sql = "DELETE FROM $this->table WHERE $placeholder";

            $statement = $this->db->prepare($sql);

            foreach ($attributes as $key => $value) {
                $statement->bindValue(":$key", $value);
            }

            if ($statement->execute()) {
                return true;
            }

        }

        return false;
    }

    public function has_one(string $table)
    {
        $table = explode('\\', $table);

        $table = strtolower(end($table));

        $sql = "SELECT `{$table}`.* 
            FROM `{$table}` 
            INNER JOIN `{$this->table}`
            ON `{$this->table}`.`{$this->primary_key}` = `{$table}`.`{$this->table}_{$this->primary_key}`
            WHERE `{$table}`.`id` = :id
        ";

        echo $sql;

        $statement = $this->db->prepare($sql);
        $statement->bindParam(':id', $this->id);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_OBJ);
    }


/* public function has_many(string $table)
 {
 $table = explode('\\', $table);
 $table = strtolower(end($table));
 $sql = "SELECT `{$table}`.* 
 FROM `{$table}` 
 INNER JOIN `{$this->table}_{$table}`
 ON `{$table}`.`{$this->primary_key}` = `{$this->table}_{$table}`.`{$table}_{$this->primary_key}`
 INNER JOIN `{$this->table}`
 ON `{$this->table}`.`{$this->primary_key}` = `{$this->table}_{$table}`.`{$this->table}_{$this->primary_key}`
 WHERE `{$table}`.`id` = :id";
 $statement = $this->db->prepare($sql);
 $statement->bindParam(':id', $this->id);
 $statement->execute();
 return $statement->fetch(PDO::FETCH_OBJ);
 }
 */



/* public function update($data)
 {
 $data = is_object($data) ? (array)$data : $data;
 $attributes = [];
 foreach ($data as $key => $value) {
 if (property_exists($this, $key)) {
 if ($key != 'created_at' && $key != 'updated_at') {
 $attributes[$key] = $value;
 }
 }
 }
 $attribute = array_keys($attributes);
 $placeholder = implode(', ', array_map(fn($attr) => " `$attr` = :$attr", $attribute));
 $sql = "UPDATE $this->table SET $placeholder WHERE $this->primary_key = :primary_key";
 $statement = $this->db->prepare($sql);
 $statement->bindValue(":primary_key", $this->{ $this->primary_key});
 foreach ($attributes as $key => $value) {
 $statement->bindValue(":$key", $value);
 }
 if ($statement->execute()) {
 return $this->find($this->{ $this->primary_key});
 }
 return false;
 } */



/* public function search($data)
 {
 $data = is_object($data) ? (array)$data : $data;
 $attributes = [];
 foreach ($data as $key => $value) {
 if (property_exists($this, $key)) {
 $attributes[$key] = $value;
 }
 }
 $attribute = array_keys($attributes);
 $sql = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $attribute));
 $statement = $this->db->prepare("SELECT * FROM $this->table WHERE $sql");
 foreach ($attributes as $key => $value) {
 $statement->bindValue(":$key", $value);
 }
 $statement->execute();
 return $statement->fetchObject(static::class);
 } */
/* public function where($attribute, $value)
 {
 $sql = "SELECT * FROM `{$this->table}` WHERE `$attribute` = :$attribute";
 $statement = $this->db->prepare($sql);
 $statement->bindParam(":$attribute", $value);
 if ($statement->execute()) {
 $attributes = $statement->fetch(PDO::FETCH_OBJ);
 if ($attributes) {
 foreach ($attributes as $key => $value) {
 $this->{ $key} = (string)$value;
 }
 return $this;
 }
 }
 return false;
 } */
/* public function save()
 {
 if (isset($this->{ $this->primary_key})) {
 return $this->update($this);
 }
 return $this->insert($this);
 } */
}