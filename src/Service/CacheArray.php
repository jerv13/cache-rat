<?php

namespace Reliv\CacheRat\Service;

/**
 * @todo   Throw exceptions per PSR docs
 *
 * @author James Jervis - https://github.com/jerv13
 */
class CacheArray implements Cache
{
    const VALUE = 'value';
    const TTL = 'ttl';
    const CREATE_TIME = 'createTime';

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->values[$key][static::VALUE];
        }

        return $default;
    }

    /**
     * @param string   $key
     * @param mixed    $value
     * @param null|int $ttl
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $this->values[$key] = [];

        $this->values[$key][static::VALUE] = $value;
        $this->values[$key][static::TTL] = $ttl;
        $this->values[$key][static::CREATE_TIME] = time();

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            unset($this->values[$key]);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $this->values = [];

        return true;
    }

    /**
     * @param array $keys
     * @param null  $default
     *
     * @return array
     */
    public function getMultiple($keys, $default = null)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * @param array    $values
     * @param null|int $ttl
     *
     * @return bool
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * @param array $keys
     *
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (!array_key_exists($key, $this->values)) {
            return false;
        }

        $value = $this->values[$key];

        return (!$this->hasExpired($value[static::TTL], $value[static::CREATE_TIME]));
    }

    /**
     * @param int|null $ttl
     * @param int      $createdTime
     *
     * @return bool
     */
    protected function hasExpired($ttl, int $createdTime)
    {
        if ($ttl === null) {
            return false;
        }

        return (time() > ($ttl + $createdTime));
    }
}
