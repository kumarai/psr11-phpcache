<?php
declare(strict_types=1);

namespace WShafer\PSR11PhpCache\Test\Adapter;

use Cache\Adapter\Memcached\MemcachedCachePool;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use WShafer\PSR11PhpCache\Adapter\MemcachedAdapterFactory;

class MemcachedAdapterFactoryTest extends TestCase
{
    /** @var MemcachedAdapterFactory */
    protected $factory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface */
    protected $mockContainer;

    public function setup()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('memcached not installed.  Skipping test');
        }

        $this->mockContainer = $this->createMock(ContainerInterface::class);

        $this->factory = new MemcachedAdapterFactory();

        $this->assertInstanceOf(MemcachedAdapterFactory::class, $this->factory);
    }

    public function testInvokeWithService()
    {
        $cacheService = new \Memcached();

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with('my-service')
            ->willReturn($cacheService);

        $instance = $this->factory->__invoke($this->mockContainer, [
            'service' => 'my-service'
        ]);

        $this->assertInstanceOf(MemcachedCachePool::class, $instance);
    }

    public function testInvokeWithNoPersistence()
    {
        $instance = $this->factory->__invoke($this->mockContainer, [
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 20]
            ],
            'memcachedOptions' => [
                \Memcached::OPT_RECV_TIMEOUT => 1000000
            ]
        ]);

        $this->assertInstanceOf(MemcachedCachePool::class, $instance);

        $cache = $this->factory->getCache();

        $expected = [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'type' => 'TCP',
            ]
        ];

        $servers = $cache->getServerList();

        $this->assertEquals($expected, $servers);
        $this->assertEquals(1000000, $cache->getOption(\Memcached::OPT_RECV_TIMEOUT));
    }

    public function testInvokeOnlyAddsServerOnce()
    {
        $instance = $this->factory->__invoke($this->mockContainer, [
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 20],
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 20],
            ]
        ]);

        $this->assertInstanceOf(MemcachedCachePool::class, $instance);

        $cache = $this->factory->getCache();

        $expected = [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'type' => 'TCP',
            ]
        ];

        $servers = $cache->getServerList();

        $this->assertEquals($expected, $servers);
    }

    public function testInvokeWithPersistence()
    {
        $instance = $this->factory->__invoke($this->mockContainer, [
            'persistentId' => 'phpunit',
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 20],
            ]
        ]);

        $this->assertInstanceOf(MemcachedCachePool::class, $instance);

        $cache = $this->factory->getCache();

        $expected = [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'type' => 'TCP',
            ]
        ];

        $servers = $cache->getServerList();

        $this->assertEquals($expected, $servers);


        // Make a second call
        $instance = $this->factory->__invoke($this->mockContainer, [
            'persistentId' => 'phpunit',
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 20],
            ]
        ]);

        $this->assertInstanceOf(MemcachedCachePool::class, $instance);

        $cache = $this->factory->getCache();

        $servers = $cache->getServerList();

        $this->assertEquals($expected, $servers);

        // clear servers
        $cache->resetServerList();

        $this->assertEmpty($cache->getServerList());
    }

    /**
     * @expectedException \WShafer\PSR11PhpCache\Exception\InvalidConfigException
     */
    public function testInvokeMissingServersAndService()
    {
        $this->factory->__invoke($this->mockContainer, []);
    }

    /**
     * @expectedException \WShafer\PSR11PhpCache\Exception\InvalidConfigException
     */
    public function testInvokeMissingServerHost()
    {
        $this->factory->__invoke($this->mockContainer, ['servers' => ['port' => 11211]]);
    }
}
