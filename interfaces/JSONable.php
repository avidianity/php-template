<?php

namespace Interfaces;

/**
 * Allows casting into an object
 */
interface JSONable
{
    public function toJSON(): object;
}
