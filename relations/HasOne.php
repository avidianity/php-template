<?php

namespace Relations;

use Interfaces\HasRelationships;
use LogicException;
use Models\Model;
use PDO;

class HasOne implements HasRelationships
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
        $query .= 'WHERE ' . $this->foreignKey . ' = :' . $this->foreignKey . ' ';
        $query .= 'LIMIT 1;';

        $statement = $pdo->prepare($query);
        $statement->execute([':' . $this->foreignKey => $this->instance->{$this->ownerKey}]);

        if ($statement->rowCount() === 0) {
            return null;
        }

        $class = $this->child;

        return (new $class())->forceFill($statement->fetchAll(PDO::FETCH_ASSOC)[0]);
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
        return $this->get()->update($data);
    }

    public function delete()
    {
        if (!$this->has()) {
            throw new LogicException('Parent does not have a child to delete.');
        }
        return $this->get()->delete();
    }

    public function has(): bool
    {
        return $this->get() !== null;
    }
}
