<?php

namespace App\Traits;
use App\Libraries\BanningScope;

trait BannAble
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootBannAble()
    {
        static::addGlobalScope(new BanningScope);
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    public function ban()
    {
        $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());

        $time = $this->freshTimestamp();

        $columns = [$this->getBannedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getBannedAtColumn()} = $time;

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        if ($query->update($columns)) {
            $this->syncOriginal();
        }
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool|null
     */
    public function unban()
    {
        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($this->fireModelEvent('unbanning') === false) {
            return false;
        }

        $this->{$this->getBannedAtColumn()} = null;

        $result = $this->save();

        $this->fireModelEvent('unbanned', false);

        return $result;
    }

    /**
     * Determine if the model instance has been soft-deleted.
     *
     * @return bool
     */
    public function isBanned()
    {
        return ! is_null($this->{$this->getBannedAtColumn()});
    }

    /**
     * Register a restoring model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function banning($callback)
    {
        static::registerModelEvent('banning', $callback);
    }

    /**
     * Register a restored model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function banned($callback)
    {
        static::registerModelEvent('banned', $callback);
    }

    /**
     * Get the name of the "deleted at" column.
     *
     * @return string
     */
    public function getBannedAtColumn()
    {
        return defined('static::BANNED_AT') ? static::BANNED_AT : 'banned_at';
    }

    /**
     * Get the fully qualified "deleted at" column.
     *
     * @return string
     */
    public function getQualifiedBannedAtColumn()
    {
        return $this->qualifyColumn($this->getBannedAtColumn());
    }
}