<?php

namespace Jeffgreco13\FilamentWave\REST;

use ArrayAccess;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class Resource implements ArrayAccess, Arrayable
{
    protected array $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get all of the attributes except for a specified array of keys.
     *
     * @param  array|string  $keys
     */
    public function except($keys): array
    {
        return Arr::except($this->getAttributes(), is_array($keys) ? $keys : func_get_args());
    }

    /**
     * Get a subset of the attributes.
     *
     * @param  array|string  $keys
     */
    public function only($keys): array
    {
        return Arr::only($this->getAttributes(), is_array($keys) ? $keys : func_get_args());
    }

    /**
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->getAttribute($key);
        }

        throw new Exception('Property '.$key.' does not exist on '.get_called_class());
    }

    /**
     * @param  string  $key
     */
    public function __isset($key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        return $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Get an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttribute($key)
    {
        return $this->attributes[$key];
    }

    /**
     * Set an attribute.
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    protected function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get attributes for the resource.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function toArray()
    {
        return $this->getAttributes();
    }
}
