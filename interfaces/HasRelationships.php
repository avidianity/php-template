<?php

namespace Interfaces;

interface HasRelationships
{
    public function get();
    public function create($data);
    public function update($data);
    public function delete();
    public function has(): bool;
}
