<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Sync;

use Oro\Bundle\EmailBundle\Sync\AbstractEmailSynchronizer;
use Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity\TestEmailOrigin;
use Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizer;

class AbstractEmailSynchronizerTest extends \PHPUnit_Framework_TestCase
{
    /** @var TestEmailSynchronizer */
    private $sync;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $log;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $emailEntityBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $emailAddressManager;

    protected function setUp()
    {
        $this->log = $this->getMock('Psr\Log\LoggerInterface');
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailEntityBuilder = $this->getMockBuilder('Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailAddressManager = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->sync = new TestEmailSynchronizer(
            $this->em,
            $this->emailEntityBuilder,
            $this->emailAddressManager
        );

        $this->sync->setLogger($this->log);
    }

    public function testSyncNoOrigin()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $maxConcurrentTasks = 3;
        $minExecPeriodInMin = 1;

        $sync = $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizer')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'resetHangedOrigins',
                    'findOriginToSync',
                    'createSynchronizationProcessor',
                    'changeOriginSyncState',
                    'getCurrentUtcDateTime'
                )
            )
            ->getMock();
        $sync->setLogger($this->log);

        $sync->expects($this->once())
            ->method('getCurrentUtcDateTime')
            ->will($this->returnValue($now));
        $sync->expects($this->once())
            ->method('resetHangedOrigins');
        $sync->expects($this->once())
            ->method('findOriginToSync')
            ->with($maxConcurrentTasks, $minExecPeriodInMin)
            ->will($this->returnValue(null));
        $sync->expects($this->never())
            ->method('createSynchronizationProcessor');

        $sync->sync($maxConcurrentTasks, $minExecPeriodInMin);
    }

    public function testDoSyncOrigin()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $origin = new TestEmailOrigin(123);

        $processor =
            $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizationProcessor')
                ->disableOriginalConstructor()
                ->getMock();

        $sync = $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizer')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'findOriginToSync',
                    'createSynchronizationProcessor',
                    'changeOriginSyncState',
                    'getCurrentUtcDateTime'
                )
            )
            ->getMock();
        $sync->setLogger($this->log);

        $sync->expects($this->once())
            ->method('getCurrentUtcDateTime')
            ->will($this->returnValue($now));
        $sync->expects($this->once())
            ->method('createSynchronizationProcessor')
            ->with($this->identicalTo($origin))
            ->will($this->returnValue($processor));
        $sync->expects($this->at(1))
            ->method('changeOriginSyncState')
            ->with($this->identicalTo($origin), AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS)
            ->will($this->returnValue(true));
        $processor->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($origin));
        $sync->expects($this->at(3))
            ->method('changeOriginSyncState')
            ->with(
                $this->identicalTo($origin),
                AbstractEmailSynchronizer::SYNC_CODE_SUCCESS,
                $this->equalTo($now)
            );

        $sync->callDoSyncOrigin($origin);
    }

    public function testDoSyncOriginForInProcessItem()
    {
        $origin = new TestEmailOrigin(123);

        $processor =
            $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizationProcessor')
                ->disableOriginalConstructor()
                ->getMock();

        $sync = $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizer')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'findOriginToSync',
                    'createSynchronizationProcessor',
                    'changeOriginSyncState',
                    'getCurrentUtcDateTime'
                )
            )
            ->getMock();
        $sync->setLogger($this->log);

        $sync->expects($this->never())
            ->method('getCurrentUtcDateTime');
        $sync->expects($this->once())
            ->method('createSynchronizationProcessor')
            ->with($this->identicalTo($origin))
            ->will($this->returnValue($processor));
        $sync->expects($this->once())
            ->method('changeOriginSyncState')
            ->with($this->identicalTo($origin), AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS)
            ->will($this->returnValue(false));
        $processor->expects($this->never())
            ->method('process');

        $sync->callDoSyncOrigin($origin);
    }

    /**
     * @expectedException \Exception
     */
    public function testDoSyncOriginProcessFailed()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $origin = new TestEmailOrigin(123);

        $processor =
            $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizationProcessor')
                ->disableOriginalConstructor()
                ->getMock();

        $sync = $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizer')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'findOriginToSync',
                    'createSynchronizationProcessor',
                    'changeOriginSyncState',
                    'getCurrentUtcDateTime'
                )
            )
            ->getMock();
        $sync->setLogger($this->log);

        $sync->expects($this->once())
            ->method('getCurrentUtcDateTime')
            ->will($this->returnValue($now));
        $sync->expects($this->once())
            ->method('createSynchronizationProcessor')
            ->with($this->identicalTo($origin))
            ->will($this->returnValue($processor));
        $sync->expects($this->at(1))
            ->method('changeOriginSyncState')
            ->with($this->identicalTo($origin), AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS)
            ->will($this->returnValue(true));
        $processor->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($origin))
            ->will($this->throwException(new \Exception()));
        $sync->expects($this->at(3))
            ->method('changeOriginSyncState')
            ->with($this->identicalTo($origin), AbstractEmailSynchronizer::SYNC_CODE_FAILURE);

        $sync->callDoSyncOrigin($origin);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDoSyncOriginSetFailureFailed()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $origin = new TestEmailOrigin(123);

        $processor =
            $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizationProcessor')
                ->disableOriginalConstructor()
                ->getMock();

        $sync = $this->getMockBuilder('Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizer')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'findOriginToSync',
                    'createSynchronizationProcessor',
                    'changeOriginSyncState',
                    'getCurrentUtcDateTime'
                )
            )
            ->getMock();
        $sync->setLogger($this->log);

        $sync->expects($this->once())
            ->method('getCurrentUtcDateTime')
            ->will($this->returnValue($now));
        $sync->expects($this->once())
            ->method('createSynchronizationProcessor')
            ->with($this->identicalTo($origin))
            ->will($this->returnValue($processor));
        $sync->expects($this->at(1))
            ->method('changeOriginSyncState')
            ->with($this->identicalTo($origin), AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS)
            ->will($this->returnValue(true));
        $processor->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($origin))
            ->will($this->throwException(new \InvalidArgumentException()));
        $sync->expects($this->at(3))
            ->method('changeOriginSyncState')
            ->with($this->identicalTo($origin), AbstractEmailSynchronizer::SYNC_CODE_FAILURE)
            ->will($this->throwException(new \Exception()));

        $sync->callDoSyncOrigin($origin);
    }

    /**
     * @dataProvider changeOriginSyncStateProvider
     */
    public function testChangeOriginSyncState($syncCode, $hasSynchronizedAt)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $origin = new TestEmailOrigin(123);

        $q = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMockForAbstractClass();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('o')
            ->will($this->returnValue($qb));

        $index = 0;
        $qb->expects($this->at($index++))
            ->method('update')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('set')
            ->with('o.syncCode', ':code')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('set')
            ->with('o.syncCodeUpdatedAt', ':updated')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('where')
            ->with('o.id = :id')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('code', $syncCode)
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('updated', $this->equalTo($now))
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('id', $origin->getId())
            ->will($this->returnValue($qb));
        if ($hasSynchronizedAt) {
            $qb->expects($this->at($index++))
                ->method('set')
                ->with('o.synchronizedAt', ':synchronized')
                ->will($this->returnValue($qb));
            $qb->expects($this->at($index++))
                ->method('setParameter')
                ->with('synchronized', $now)
                ->will($this->returnValue($qb));
        }
        if ($syncCode === AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS) {
            $qb->expects($this->at($index++))
                ->method('andWhere')
                ->with('(o.syncCode IS NULL OR o.syncCode <> :code)')
                ->will($this->returnValue($qb));
        }
        $qb->expects($this->at($index++))
            ->method('getQuery')
            ->will($this->returnValue($q));
        $q->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(1));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(TestEmailSynchronizer::EMAIL_ORIGIN_ENTITY)
            ->will($this->returnValue($repo));

        $this->sync->setCurrentUtcDateTime($now);
        $result = $this->sync->callChangeOriginSyncState($origin, $syncCode, $hasSynchronizedAt ? $now : null);
        $this->assertTrue($result);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFindOriginToSync()
    {
        $maxConcurrentTasks = 2;
        $minExecPeriodInMin = 1;

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $border = clone $now;
        if ($minExecPeriodInMin > 0) {
            $border->sub(new \DateInterval('PT' . $minExecPeriodInMin . 'M'));
        }
        $min = clone $now;
        $min->sub(new \DateInterval('P1Y'));

        $q = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('o')
            ->will($this->returnValue($qb));

        $index = 0;
        $qb->expects($this->at($index++))
            ->method('select')
            ->with(
                'o'
                . ', CASE WHEN o.syncCode = :inProcess THEN 0 ELSE 1 END AS HIDDEN p1'
                . ', (COALESCE(o.syncCode, 1000) * 100'
                . ' + (:now - COALESCE(o.syncCodeUpdatedAt, :min))'
                . ' / (CASE o.syncCode WHEN :success THEN 100 ELSE 1 END)) AS HIDDEN p2'
            )
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('where')
            ->with('o.isActive = :isActive AND o.syncCodeUpdatedAt IS NULL OR o.syncCodeUpdatedAt <= :border')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('orderBy')
            ->with('p1, p2 DESC, o.syncCodeUpdatedAt')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('inProcess', AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS)
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('success', AbstractEmailSynchronizer::SYNC_CODE_SUCCESS)
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('isActive', true)
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('now', $this->equalTo($now))
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('min', $this->equalTo($min))
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('border', $this->equalTo($border))
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setMaxResults')
            ->with($maxConcurrentTasks + 1)
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('getQuery')
            ->will($this->returnValue($q));

        $origin1 = new TestEmailOrigin(1);
        $origin1->setSyncCode(AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS);
        $origin2 = new TestEmailOrigin(2);
        $origin2->setSyncCode(AbstractEmailSynchronizer::SYNC_CODE_SUCCESS);
        $origin3 = new TestEmailOrigin(3);
        $q->expects($this->once())
            ->method('getResult')
            ->will(
                $this->returnValue(
                    array($origin1, $origin2, $origin3)
                )
            );

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(TestEmailSynchronizer::EMAIL_ORIGIN_ENTITY)
            ->will($this->returnValue($repo));

        $this->sync->setCurrentUtcDateTime($now);
        $result = $this->sync->callFindOriginToSync($maxConcurrentTasks, $minExecPeriodInMin);

        $this->assertEquals($origin2, $result);
    }

    public function testResetHangedOrigins()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $border = clone $now;
        $border->sub(new \DateInterval('P1D'));

        $q = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMockForAbstractClass();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('o')
            ->will($this->returnValue($qb));

        $index = 0;
        $qb->expects($this->at($index++))
            ->method('update')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('set')
            ->with('o.syncCode', ':failure')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('where')
            ->with('o.syncCode = :inProcess AND o.syncCodeUpdatedAt <= :border')
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('inProcess', AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS)
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('failure', AbstractEmailSynchronizer::SYNC_CODE_FAILURE)
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('setParameter')
            ->with('border', $this->equalTo($border))
            ->will($this->returnValue($qb));
        $qb->expects($this->at($index++))
            ->method('getQuery')
            ->will($this->returnValue($q));

        $q->expects($this->once())
            ->method('execute');

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(TestEmailSynchronizer::EMAIL_ORIGIN_ENTITY)
            ->will($this->returnValue($repo));

        $this->sync->setCurrentUtcDateTime($now);
        $this->sync->callResetHangedOrigins();
    }

    public function changeOriginSyncStateProvider()
    {
        return array(
            array(AbstractEmailSynchronizer::SYNC_CODE_FAILURE, false),
            array(AbstractEmailSynchronizer::SYNC_CODE_IN_PROCESS, false),
            array(AbstractEmailSynchronizer::SYNC_CODE_SUCCESS, true),
        );
    }
}
