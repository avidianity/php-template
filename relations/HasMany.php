<?php

namespace Relations;

use Interfaces\HasRelationships;
use LogicException;
use Models\Model;
use PDO;

class HasMany implements HasRelationships
{
    protected $child;
    protected $foreignKey;
    protected $ownerKey;

    /**
     * @var Model
     */
    protected $instance;

    public function __construct($child, $foreignKey = null, $ownerKey = 'id', Model &$instance)
    {
        $this->instance = $instance;
        $this->child = $child;
        $this->ownerKey = $ownerKey;
        $this->foreignKey = $foreignKey !== null
            ? $foreignKey
            : $this->qualifyForeignKey($child);
    }

    protected function qualifyForeignKey($class)
    {
        return $this->instance->getTable() . '_id';
    }

    protected function getChildTable()
    {
        $class = $this->child;
        return (new $class())->getTable();
    }

    public function get()
    {
        $pdo = Model::getConnection();

        $query  = 'SELECT * FROM ' . $this->getChildTable() . ' ';
        $query .= 'WHERE ' . $this->foreignKey . ' = :' . $this->foreignKey . ';';

        $statement = $pdo->prepare($query);
        $statement->execute([':' . $this->foreignKey => $this->instance->{$this->ownerKey}]);

        if ($statement->rowCount() === 0) {
            return [];
        }

        $class = $this->child;

        return array_map(function ($row) use ($class) {
            return (new $class())->forceFill($row);
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create($data)
    {
        $child = $this->child;
        $data[$this->foreignKey] = $this->instance->{$this->ownerKey};
        return $child::create($data);
    }

    public function update($data)
    {
        if (!$this->has()) {
            throw new LogicException('Parent does not have a child to update.');
        }
        return array_map(function ($row) use ($data) {
            return $row->update($data);
        }, $this->get());
    }

    public function delete()
    {
        if (!$this->has()) {
            throw new LogicException('Parent does not have a child to delete.');
        }
        return array_map(function ($row) {
            return $row->delete();
        }, $this->get());
    }

    public function has(): bool
    {
        return count($this->get()) > 0;
    }
}
