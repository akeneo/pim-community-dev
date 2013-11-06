<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Calculator;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Calculator\CompletenessCalculator;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->repository    = $this->getCompletenessRepositoryMock();
        $this->entityManager = $this->getEntityManagerMock($this->repository);
        $this->calculator    = new CompletenessCalculator($this->entityManager);
    }

    public function testSchedule()
    {
        $product = $this->getProductMock();
        $query = $this->getQueryMock();

        $this->entityManager
            ->expects($this->once())
            ->method('createQuery')
            ->with('DELETE FROM Pim\Bundle\CatalogBundle\Entity\Completeness c WHERE c.product = :product')
            ->will($this->returnValue($query));

        $query->expects($this->once())
            ->method('setParameter')
            ->with('product', $product);

        $query->expects($this->once())
            ->method('execute');

        $this->calculator->schedule($product);
    }

    public function testCalculateChannelCompleteness()
    {
        $channel = $this->getChannelMock();
        $this->repository
            ->expects($this->once())
            ->method('createChannelCompletenesses')
            ->with($channel);

        $this->calculator->calculateChannelCompleteness($channel);
    }

    public function testCalculateProductCompleteness()
    {
        $product = $this->getProductMock();
        $this->repository
            ->expects($this->once())
            ->method('createProductCompletenesses')
            ->with($product);

        $this->calculator->calculateProductCompleteness($product);
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock($repository)
    {
        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        return $em;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Repository\CompletenessRepository
     */
    protected function getCompletenessRepositoryMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Repository\CompletenessRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Completeness
     */
    protected function getCompletenessMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Completeness');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Product
     */
    private function getProductMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');
    }

    /**
     * @return Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChannelMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');
    }

    protected function getQueryMock()
    {
        return $this->getMockForAbstractClass(
            'Doctrine\ORM\AbstractQuery',
            array(),
            '',
            false,
            false,
            true,
            array('execute', 'setParameter')
        );
    }
}
