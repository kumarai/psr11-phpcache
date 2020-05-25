<?php

declare(strict_types=1);

namespace WShafer\PSR11PhpCacheTests\Config;

use PHPUnit\Framework\TestCase;
use WShafer\PSR11PhpCache\Config\Config;
use WShafer\PSR11PhpCache\Config\ConfigCollection;

/**
 * @covers \WShafer\PSR11PhpCache\Config\ConfigCollection
 */
class ConfigCollectionTest extends TestCase
{
    protected $collection;

    protected function setUp(): void
    {
        $this->collection = new ConfigCollection(
            [
                'default' => [
                    'type' => 'defaultType'
                ],
                'cacheOne' => [
                    'type' => 'cacheOneType'
                ]
            ]
        );
    }

    public function testGetCacheConfigDefault(): void
    {
        $result = $this->collection->getCacheConfig();

        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals('defaultType', $result->getType());
    }

    public function testGetCacheConfigCacheOne(): void
    {
        $result = $this->collection->getCacheConfig('cacheOne');

        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals('cacheOne', $result->getType());
    }
}