<?php
namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Cache;

use Oro\Bundle\SecurityBundle\Acl\Cache\AclCache;

class AclCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclCache */
    protected $aclCache;

    protected $cacheProvider;
    protected $permissionGrantingStrategy;
    protected $prefix;

    protected function setUp(): void
    {
        $this->cacheProvider = $this->createMock('Doctrine\Common\Cache\CacheProvider', [
            'deleteAll', 'doFetch', 'doContains', 'doSave', 'doDelete', 'doFlush', 'doGetStats'
        ]
        );
        $this->permissionGrantingStrategy =
            $this->getMockForAbstractClass('Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface');
        $this->prefix = 'test_prefix';
        $this->aclCache = new AclCache($this->cacheProvider, $this->permissionGrantingStrategy, $this->prefix);
    }

    public function testClearCache()
    {
        $this->cacheProvider->expects($this->once())->method('deleteAll');

        $this->aclCache->clearCache();
    }
}
