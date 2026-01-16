<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Service to test and demonstrate Redis cache functionality
 */
class RedisCacheService
{
    public function __construct(
        private CacheInterface $cache,
        private CacheItemPoolInterface $cachePool
    ) {
    }

    /**
     * Test if Redis connection is working
     */
    public function testConnection(): bool
    {
        try {
            $testKey = 'redis_connection_test';
            $testValue = 'Redis is working! ' . date('Y-m-d H:i:s');
            
            // Try to set and get a value
            $result = $this->cache->get($testKey, function (ItemInterface $item) use ($testValue) {
                $item->expiresAfter(10); // Expire after 10 seconds
                return $testValue;
            });
            
            return $result === $testValue;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Store data in cache with expiration
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        try {
            $this->cache->get($key, function (ItemInterface $item) use ($value, $ttl) {
                $item->expiresAfter($ttl);
                return $value;
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get data from cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            $item = $this->cachePool->getItem($key);
            return $item->isHit() ? $item->get() : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Delete a cache entry
     */
    public function delete(string $key): bool
    {
        try {
            return $this->cachePool->deleteItem($key);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        try {
            return $this->cachePool->clear();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'adapter' => get_class($this->cache),
            'pool' => get_class($this->cachePool),
            'connection_test' => $this->testConnection() ? 'OK' : 'FAILED'
        ];
    }
}
