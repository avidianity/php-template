<?php

namespace Models;

use Interfaces\Arrayable;
use Interfaces\JSONable;
use PDO;
use stdClass;
use PDOException;

abstract class Model implements JSONable, Arrayable
{
    /**
     * Current data associated with this model
     * 
     * @var mixed
     */
    protected $data = [];

    /**
     * Properties that can be mass-assigned
     * 
     * @var mixed
     */
    protected $fillable = [];

    /**
     * Properties that should be hidden when serialized.
     * 
     * @var mixed
     */
    protected $hidden = [];

    /**
     * The model's table name. It will be inferred if its null
     * 
     * @var null|string
     */
    protected static $table = null;

    /**
     * Current database connection used
     * @var \PDO
     */
    protected static $pdo = null;

    /**
     * Create a new instance of the model and fill any data if any
     * 
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->fill($data);
        }
    }

    /**
     * Magically set a value into the data
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Magically get a value from the data
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Mass-assign values into the the data
     * 
     * @param mixed|array $data
     * @return static
     */
    public function fill($data)
    {
        return $this->forceFill(only($data, $this->hidden));
    }

    /**
     * Force mass-assign values by ignoring the fillable array
     * 
     * @param mixed|array $data
     * @return static
     */
    public function forceFill($data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * Get the model's table name
     * 
     * @return string
     */
    public function getTable()
    {
        if (static::$table !== null) {
            return static::$table;
        }
        $split = explode('\\', get_class($this));
        return strtolower($split[count($split) - 1]);
    }

    /**
     * Serialize the model's data into an object
     * 
     * @return object
     */
    public function toJSON(): object
    {
        $object = new stdClass();

        foreach ($this->toArray() as $property => $value) {
            $object->{$property} = $value;
        }

        return $object;
    }

    /**
     * Serialize the model's data into an array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return except($this->data, $this->hidden);
    }

    /**
     * Set the current connection
     * 
     * @param \PDO $pdo
     * @return void
     */
    public static function setConnection($pdo)
    {
        static::$pdo = $pdo;
    }

    /**
     * Get the current connection
     * 
     * @return \PDO;
     */
    public static function getConnection()
    {
        return static::$pdo;
    }

    /**
     * Create a new entry in the database
     * 
     * @param mixed $data
     * @return static
     */
    public static function create($data)
    {
        $instance = new static();
        unset($data['created_at']);
        unset($data['updated_at']);

        $table = $instance->getTable();

        $query  = 'INSERT INTO ' . $table . ' (';
        $query .= implode(', ', array_keys($data)) . ') VALUES (';
        $query .= implode(', ', array_map(function ($key) {
            return ':' . $key;
        }, array_keys($data))) . ');';

        $statement = static::$pdo->prepare($query);
        if (!$statement) {
            throw new PDOException('Unable to prepare PDO statement.');
        }

        $inputs = [];

        foreach ($data as $key => $value) {
            $inputs[":{$key}"] = $value;
        }

        $statement->execute($inputs);

        $id = static::$pdo->lastInsertId();

        return static::find($id);
    }

    /**
     * Update current entry to the database
     * 
     * @param mixed $data
     * @return static
     */
    public function update($data = [])
    {
        $this->fill($data);

        $data = $this->data;
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);

        $table = $this->getTable();

        $query  = 'UPDATE ' . $table . ' SET ';

        $params = [];

        foreach (array_keys($data) as $key) {
            $params[] = $key . ' = :' . $key;
        }

        $query .= implode(', ', $params) . ' ';

        $query .= 'WHERE id = :id;';

        $statement = static::$pdo->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        if (!$statement) {
            throw new PDOException('Unable to prepare PDO statement.');
        }

        $inputs = [
            ':id' => $this->id,
        ];

        foreach ($data as $key => $value) {
            $inputs[":{$key}"] = $value;
        }

        $statement->execute($inputs);

        return static::find($this->id);
    }

    /**
     * Save the current instance into the database
     * 
     * @return static
     */
    public function save()
    {
        return in_array('id', $this->data)
            ? $this->update()
            : static::create($this->data);
    }

    /**
     * Deletes the current instance from the database
     * 
     * @return static
     */
    public function delete()
    {
        $statement = static::$pdo->prepare('DELETE FROM ' . $this->getTable() . ' WHERE id = :id;');
        if (!$statement) {
            throw new PDOException('Unable to prepare PDO statement.');
        }

        $statement->execute([':id' => $this->id]);

        return $this;
    }

    /**
     * Deletes the instances or ids from the database
     * 
     * @param array|static[] $ids
     * @return void
     */
    public static function deleteMany($ids = [])
    {
        $ids = array_map(function ($entry) {
            if ($entry instanceof static) {
                return $entry->id;
            }
            return $entry;
        }, $ids);
        $instance = new static();
        $query = 'DELETE FROM ' . $instance->getTable() . ' WHERE id IN(' . implode(', ', array_map(function () {
            return '?';
        }, $ids)) . ');';
        $statement = static::$pdo->prepare($query);
        if (!$statement) {
            throw new PDOException('Unable to prepare PDO statement.');
        }

        $statement->execute($ids);
    }

    /**
     * Gets all rows from the database
     * 
     * @return static[]
     */
    public static function getAll()
    {
        $statement = static::$pdo->query('SELECT * FROM ' . (new static())->getTable() . ';');
        if (!$statement) {
            throw new PDOException('Unable to prepare PDO statement.');
        }

        return array_map(function ($row) {
            $instance = (new static)->forceFill($row);
            return $instance;
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Finds an id or ids in the database
     * 
     * @param int|int[] $ids
     * @return static|static[]
     */
    public static function find($ids)
    {
        $single = false;
        if (!is_array($ids)) {
            $single = true;
            $ids = [$ids];
        }

        $query  = 'SELECT * FROM ' . (new static())->getTable() . ' ';
        $query .= 'WHERE id IN (' . implode(', ', array_map(function () {
            return '?';
        }, $ids)) . ');';

        $statement = static::$pdo->prepare($query);
        if (!$statement) {
            throw new PDOException('Unable to prepare PDO statement.');
        }

        $statement->execute($ids);

        if ($statement->rowCount() === 0 && $single) {
            return null;
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($single) {
            return (new static())->forceFill($result[0]);
        }
        return array_map(function ($row) {
            return (new static())->forceFill($row);
        }, $result);
    }
}
