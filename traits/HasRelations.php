<?php

namespace Traits;

use Relations\BelongsTo;
use Relations\HasMany;
use Relations\HasOne;

trait HasRelations
{
    public function hasMany($class, $foreignKey = null, $ownerKey = 'id')
    {
        return new HasMany($class, $foreignKey, $ownerKey, $this);
    }

    public function hasOne($class, $foreignKey = null, $ownerKey = 'id')
    {
        return new HasOne($class, $foreignKey, $ownerKey, $this);
    }

    public function belongsTo($class, $foreignKey = null, $ownerKey = 'id')
    {
        return new BelongsTo($class, $foreignKey, $ownerKey, $this);
    }
}
