<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Calculator;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Pim\Bundle\ProductBundle\Entity\AttributeRequirement;

use Pim\Bundle\ProductBundle\Entity\Channel;
use Pim\Bundle\ProductBundle\Entity\Locale;

use Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator
     */
    protected $calculator;

    protected $channel1;
    protected $channel2;

    protected $locale1;
    protected $locale2;

    protected $attribute1;
    protected $attribute2;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->initializeAttributes();
        $this->initializeChannels();
        $this->initializeLocales();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $channelManager = $this->createChannelManager();
        $localeManager  = $this->createLocaleManager();
        $entityManager  = $this->createEntityManager();
        $validator      = $this->createValidator();

        $this->calculator = new CompletenessCalculator($channelManager, $localeManager, $entityManager, $validator);
    }

    /**
     * Data provider for calculator
     * @return array
     */
    public function dataProviderCalculator()
    {
        return array(
            array(
                'first test' => array(
                    array(
                        'attribute' => $this->attribute1->getCode(),
                        'locale' => $this->locale1->getCode(),
                        'channel' => $this->channel1->getCode(),
                        'return' => $this->concatValues($this->attribute1, $this->locale1, $this->channel1)
                    ),
                    array(
                        'attribute' => $this->attribute1->getCode(),
                        'locale' => $this->locale2->getCode(),
                        'channel' => $this->channel1->getCode(),
                        'return' => $this->concatValues($this->attribute1, $this->locale2, $this->channel1)
                    ),
                )
            )
        );
    }

    /**
     * Concat values
     * @return string
     */
    private function concatValues($attribute, $locale, $channel)
    {
        return $attribute->getCode() .'_'. $locale->getCode() .'_'. $channel->getCode();
    }

    /**
     * Test related method
     *
     * @param array $values Array of product values
     * array(
     *     array('locale' => locale1, 'channel' => channel1, 'return' => product value),
     *     ...
     * )
     *
     * @dataProvider dataProviderCalculator
     */
    public function testCalculatorForAProductByChannel($values = array())
    {
        $product = $this->createProductMock($values);

        $completenesses = $this->calculator->calculateForAProductByChannel($product, $this->channel1);
        $this->assertCount(2, $completenesses);

        $product->setCompletenesses($completenesses);

        $completeness = $product->getCompleteness($this->locale1->getCode(), $this->channel1->getCode());
        $this->assertEquals(100, $completeness->getRatio());
    }

    /**
     * Create a product mock
     *
     * @param array $values
     *
     * @return Product
     */
    protected function createProductMock($values)
    {
        $product = $this->getMock('Pim\Bundle\ProductBundle\Entity\Product', array('getValue'));

        $product
            ->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($values));

        return $product;
    }

    /**
     * Initialize channels
     */
    protected function initializeChannels()
    {
        $this->channel1 = $this->createChannel('ecommerce');
        $this->channel2 = $this->createChannel('ipad');
    }

    /**
     * Initialize locales
     */
    protected function initializeLocales()
    {
        $this->locale1 = $this->createLocale('en_US');
        $this->locale2 = $this->createLocale('fr_FR');
    }

    /**
     * Initialize attributes
     */
    protected function initializeAttributes()
    {
        $this->attribute1 = $this->createAttribute('attr_1');
        $this->attribute2 = $this->createAttribute('attr_2');
    }

    /**
     * Create channel manager mock
     *
     * @return \Pim\Bundle\ProductBundle\Manager\ChannelManager
     */
    protected function createChannelManager()
    {
        $channelManager = $this->getMockBuilder('Pim\Bundle\ProductBundle\Manager\ChannelManager')
                               ->disableOriginalConstructor()
                               ->getMock();

        $channelList = $this->getChannelList();

        $channelManager
            ->expects($this->any())
            ->method('getChannels')
            ->will($this->returnValue($channelList));

        return $channelManager;
    }

    /**
     * Get channel list
     *
     * @return array
     */
    protected function getChannelList()
    {
        return array(
            $this->channel1,
            $this->channel2
        );
    }

    /**
     * Create channel
     *
     * @param string $code
     *
     * @return \Pim\Bundle\ProductBundle\Tests\Unit\Calculator\Channel
     */
    protected function createChannel($code)
    {
        $channel = new Channel();
        $channel->setCode($code);

        return $channel;
    }

    /**
     * Create locale manager mock
     *
     * @return \Pim\Bundle\ProductBundle\Manager\LocaleManager
     */
    protected function createLocaleManager()
    {
        $localeManager = $this->getMockBuilder('Pim\Bundle\ProductBundle\Manager\LocaleManager')
                              ->disableOriginalConstructor()
                              ->getMock();

        $localeList = $this->getLocaleList();

        $localeManager
            ->expects($this->any())
            ->method('getActiveLocales')
            ->will($this->returnValue($localeList));

        return $localeManager;
    }

    /**
     * Get locale list
     *
     * @return array
     */
    protected function getLocaleList()
    {
        return array(
            $this->locale1,
            $this->locale2
        );
    }

    /**
     * Create locale
     *
     * @param string $code
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Locale
     */
    protected function createLocale($code)
    {
        $locale = new Locale();
        $locale->setCode($code);

        return $locale;
    }

    /**
     * Get entity manager mock
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function createEntityManager()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                              ->disableOriginalConstructor()
                              ->getMock();

        $repository = $this->getRepository();

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('PimProductBundle:AttributeRequirement')
            ->will($this->returnValue($repository));

        return $entityManager;
    }

    /**
     * Get attribute requirements repository mock
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                     ->disableOriginalConstructor()
                     ->getMock();

        $attributeRequirementsList = array(
            $this->createAttributeRequirement($this->attribute1, $this->channel1),
            $this->createAttributeRequirement($this->attribute1, $this->channel1),
            $this->createAttributeRequirement($this->attribute2, $this->channel2),
            $this->createAttributeRequirement($this->attribute2, $this->channel2)
        );

        $repository
            ->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue($attributeRequirementsList));

        return $repository;
    }

    /**
     * Create attribute requirement entity
     *
     * @param ProductAttribute $attribute
     * @param Channel $channel
     * @param Locale $locale
     * @param boolean $required
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeRequirement
     */
    protected function createAttributeRequirement($attribute, $channel, $required = true)
    {
        $attributeRequirement = new AttributeRequirement();
        $attributeRequirement->setAttribute($attribute);
        $attributeRequirement->setChannel($channel);

        $attributeRequirement->setRequired($required);

        return $attributeRequirement;
    }

    /**
     * Create validator mock
     */
    protected function createValidator()
    {
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator')
                          ->disableOriginalConstructor()
                          ->getMock();

        return $validator;
    }

    /**
     * Create attribute entity
     *
     * @param string $code
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function createAttribute($code)
    {
        $attribute = new ProductAttribute();
        $attribute->setCode($code);

        return $attribute;
    }
}
