<?php

namespace App\Traits;
use App\Libraries\ActivatingScope;

trait ActiveAble
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootActiveAble()
    {
        static::addGlobalScope(new ActivatingScope);
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    public function active()
    {
        $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());

        $time = $this->freshTimestamp();

        $columns = [$this->getActivatedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getActivatedAtColumn()} = $time;

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
    public function nonActive()
    {
        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($this->fireModelEvent('nonActivating') === false) {
            return false;
        }

        $this->{$this->getActivatedAtColumn()} = null;

        $result = $this->save();

        $this->fireModelEvent('nonActivated', false);

        return $result;
    }

    /**
     * Determine if the model instance has been soft-deleted.
     *
     * @return bool
     */
    public function isActived()
    {
        return ! is_null($this->{$this->getActivatedAtColumn()});
    }

    /**
     * Register a restoring model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function activating($callback)
    {
        static::registerModelEvent('activating', $callback);
    }

    /**
     * Register a restored model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function activated($callback)
    {
        static::registerModelEvent('activated', $callback);
    }

    /**
     * Get the name of the "deleted at" column.
     *
     * @return string
     */
    public function getActivatedAtColumn()
    {
        return defined('static::ACTIVATED_AT') ? static::ACTIVATED_AT : 'activated_at';
    }

    /**
     * Get the fully qualified "deleted at" column.
     *
     * @return string
     */
    public function getQualifiedActivatedAtColumn()
    {
        return $this->qualifyColumn($this->getActivatedAtColumn());
    }
}