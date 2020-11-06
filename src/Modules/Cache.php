<?php

namespace Botify\Modules;

class Cache
{
    public function __invoke($config)
    {
        $driver = $config['driver'];

        switch ($driver) {
            case 'memcached':
                $cache = new \Memcached();
                $cache->addServer($config[$driver]['host'], $config[$driver]['port']);
                break;

            case 'redis':
                $redis = new \Redis();
                $redis->connect($config[$driver]['host'], $config[$driver]['port']);
                break;

            default:
                $cache = false;
                break;
        }

        return $cache;
    }
}
