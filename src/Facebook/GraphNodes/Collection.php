<?php
/**
 * Copyright 2017 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Facebook\GraphNodes;

/**
 * Class Collection
 *
 * Modified version of Collection in "illuminate/support" by Taylor Otwell
 *
 * @package Facebook
 */

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The items contained in the collection.
     */
    protected array $items = [];

    /**
     * Create a new collection.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Gets the value of a field from the Graph node.
     *
     * @param string $name    The field to retrieve.
     * @param mixed  $default The default to return if the field doesn't exist.
     */
    public function getField(string $name, mixed $default = null): mixed
    {
        if (isset($this->items[$name])) {
            return $this->items[$name];
        }

        return $default;
    }

    /**
     * Gets the value of the named property for this graph object.
     *
     * @param string $name    The property to retrieve.
     * @param mixed  $default The default to return if the property doesn't exist.
     *
     * @deprecated 5.0.0 getProperty() has been renamed to getField()
     * @todo v6: Remove this method
     */
    public function getProperty(string $name, mixed $default = null): mixed
    {
        return $this->getField($name, $default);
    }

    /**
     * Returns a list of all fields set on the object.
     */
    public function getFieldNames(): array
    {
        return array_keys($this->items);
    }

    /**
     * Returns a list of all properties set on the object.
     *
     * @deprecated 5.0.0 getPropertyNames() has been renamed to getFieldNames()
     * @todo v6: Remove this method
     */
    public function getPropertyNames(): array
    {
        return $this->getFieldNames();
    }

    /**
     * Get all of the items in the collection.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get the collection of items as a plain array.
     */
    public function asArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof Collection ? $value->asArray() : $value;
        }, $this->items);
    }

    /**
     * Run a map over each of the items.
     */
    public function map(\Closure $callback): static
    {
        return new static(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * Get the collection of items as JSON.
     */
    public function asJson(int $options = 0): string
    {
        return json_encode($this->asArray(), $options);
    }

    /**
     * Count the number of items in the collection.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     */
    public function offsetExists(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     */
    public function offsetUnset(mixed $key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Convert the collection to its string representation.
     */
    public function __toString(): string
    {
        return $this->asJson();
    }
}
