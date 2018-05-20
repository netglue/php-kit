<?php
declare(strict_types=1);

namespace Prismic\Test\Cache;

use Prismic\Cache\DefaultCache;
use Prismic\Test\TestCase;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class DefaultCacheTest extends TestCase
{
    public function testApcUsedAsDefaultCacheIfAvailable()
    {
        if (! \extension_loaded('apc')) {
            $this->markTestSkipped('APC extension is not loaded');
            return;
        }
        if (! \ini_get('apc.enable_cli')) {
            $this->markTestSkipped('APC is disabled on the CLI');
        }

        $cache = DefaultCache::factory();
        $this->assertInstanceOf(ApcuAdapter::class, $cache);
    }

    public function testArrayCacheIsUsedByDefaultWhenApcIsNotAvailable()
    {
        if (\extension_loaded('apc')) {
            $this->markTestSkipped('APC extension is loaded so this test cannot continue');
            return;
        }
        $cache = DefaultCache::factory();
        $this->assertInstanceOf(ArrayAdapter::class, $cache);
    }

    public function testGetArrayCache()
    {
        $cache = DefaultCache::getArrayCache();
        $this->assertInstanceOf(ArrayAdapter::class, $cache);
    }
}
