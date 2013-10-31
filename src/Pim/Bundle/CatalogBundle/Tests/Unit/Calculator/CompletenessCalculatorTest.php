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
    /**
     * @var \Pim\Bundle\CatalogBundle\Calculator\CompletenessCalculator
     */
    protected $calculator;

    protected $attribute1;
    protected $attribute2;

    protected $channel1;
    protected $channel2;

    protected $family;

    protected $locale1;
    protected $locale2;

    protected $repository;

    protected $validator;

    const CHANNEL_1 = 'channel1';
    const CHANNEL_2 = 'channel2';

    const LOCALE_1  = 'en_US';
    const LOCALE_2  = 'fr_FR';

    const ATTR_1    = 'attr_1';
    const ATTR_2    = 'attr_2';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initializeAttributes();
        $this->initializeChannels();
        $this->initializeLocales();
        $this->family = new Family();

        // initialize mocks
        $this->initializeRepository();
        $this->initializeValidator();

        $channelManager = $this->createChannelManager();
        $localeManager  = $this->createLocaleManager();
        $this->entityManager  = $this->createEntityManager();

        $this->calculator = new CompletenessCalculator(
            $channelManager,
            $localeManager,
            $this->entityManager,
            $this->validator
        );
    }

    /**
     * Data provider for calculator for the method calculateForAProductByChannel
     *
     * array(
     *     channel,
     *     product values -> array(),
     *     validator errors -> array(),
     *     results expected -> array()
     * )
     *
     * @return array
     */
    public function dataProviderCalculatorForAProductByChannel()
    {
        return array(
            'all data set' => array(
                self::CHANNEL_1,
                array(
                    array('attribute' => self::ATTR_1, 'locale' => self::LOCALE_1, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_1, 'locale' => self::LOCALE_2, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_2, 'locale' => self::LOCALE_1, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_2, 'locale' => self::LOCALE_2, 'channel' => self::CHANNEL_1),
                ),
                array(array(), array(), array(), array()),
                array(
                    array(
                        'locale'  => self::LOCALE_1,
                        'channel' => self::CHANNEL_1,
                        'results' => array('ratio' => 100, 'missing_count' => 0, 'required_count' => 2)
                    ),
                    array(
                        'locale'  => self::LOCALE_2,
                        'channel' => self::CHANNEL_1,
                        'results' => array('ratio' => 100, 'missing_count' => 0, 'required_count' => 2)
                    )
                )
            ),
            'incorrect values' => array(
                self::CHANNEL_1,
                array(
                    array('attribute' => self::ATTR_1, 'locale' => self::LOCALE_1, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_1, 'locale' => self::LOCALE_2, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_2, 'locale' => self::LOCALE_1, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_2, 'locale' => self::LOCALE_2, 'channel' => self::CHANNEL_1)
                ),
                array(array('error'), array(), array(), array()),
                array(
                    array(
                        'locale'  => self::LOCALE_1,
                        'channel' => self::CHANNEL_1,
                        'results' => array('ratio' => 50, 'missing_count' => 1, 'required_count' => 2)
                    ),
                    array(
                        'locale'  => self::LOCALE_2,
                        'channel' => self::CHANNEL_1,
                        'results' => array('ratio' => 100, 'missing_count' => 0, 'required_count' => 2)
                    )
                )
            )
        );
    }

    /**
     * Test related method
     *
     * @param string $channelCode
     * @param array  $values      Array of product values
     * array(
     *     array('locale' => locale1, 'channel' => channel1, 'return' => product value),
     *     ...
     * )
     * @param array $errors  Array of the errors returned by the validator
     * @param array $results Array of expected completeness results
     * array(
     *     array('locale' => locale1, 'channel' => channel1, result => array(ratio, missing_count, required_attr)
     * )
     *
     * @dataProvider dataProviderCalculatorForAProductByChannel
     */
    public function testCalculatorForAProductByChannel($channelCode, array $values, array $errors, array $results)
    {
        $product = $this->createProductMock($values);

        // update repository mock
        $channelUsed = $this->getChannel($channelCode);
        $this->mockRepository($channelUsed);

        // update validator mock
        $this
            ->validator
            ->expects($this->any())
            ->method('validateValue')
            ->will($this->onConsecutiveCalls($errors[0], $errors[1], $errors[2], $errors[3]));

        // call the calculator
        $this->calculator->calculateForAProductByChannel($product, $channelUsed);
        $this->assertCount(count($results), $product->getCompletenesses());

        foreach ($results as $result) {
            $completeness = $product->getCompleteness($result['locale'], $result['channel']);
            $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Completeness', $completeness);

            $this->assertEquals($result['results']['ratio'], $completeness->getRatio());
            $this->assertEquals($result['results']['missing_count'], $completeness->getMissingCount());
            $this->assertEquals($result['results']['required_count'], $completeness->getRequiredCount());
        }
    }

    /**
     * Data provider for calculator for the method calculateForAProductWithoutFamilyByChannel
     *
     * array(
     *     channel,
     *     product values -> array()
     * )
     *
     * @return array
     */
    public function dataProviderCalculatorForAProductWithoutFamilyByChannel()
    {
        return array(
            'all data set' => array(
                self::CHANNEL_1,
                array(
                    array('attribute' => self::ATTR_1, 'locale' => self::LOCALE_1, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_1, 'locale' => self::LOCALE_2, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_2, 'locale' => self::LOCALE_1, 'channel' => self::CHANNEL_1),
                    array('attribute' => self::ATTR_2, 'locale' => self::LOCALE_2, 'channel' => self::CHANNEL_1),
                )
            )
        );
    }

    /**
     * Test calculateForAProduct method with a product without family
     * No completeness must be returned because there is no calculation possible without family
     *
     * @param string $channelCode
     * @param array  $values      Array of product values
     * array(
     *     array('locale' => locale1, 'channel' => channel1, 'return' => product value),
     *     ...
     * )
     *
     * @dataProvider dataProviderCalculatorForAProductWithoutFamilyByChannel
     */
    public function testCalculatorForAProductWithoutFamilyByChannel($channelCode, array $values)
    {
        $product = $this->createProductMock($values);
        $product->setFamily(null);

        // update repository mock
        $channelUsed = $this->getChannel($channelCode);
        $this->mockRepository($channelUsed);

        // call the calculator
        $this->calculator->calculateForAProductByChannel($product, $channelUsed);
        $this->assertCount(0, $product->getCompletenesses());
    }

    public function testSchedule()
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');
        $completeness1 = $this->getCompletenessMock();
        $completeness2 = $this->getCompletenessMock();
        $completeness3 = $this->getCompletenessMock();

        $product->expects($this->any())
            ->method('getCompletenesses')
            ->will(
                $this->returnValue(
                    array(
                        $completeness1,
                        $completeness2,
                        $completeness3,
                    )
                )
            );

        $this->entityManager
            ->expects($this->at(0))
            ->method('remove')
            ->with($completeness1);

        $this->entityManager
            ->expects($this->at(1))
            ->method('remove')
            ->with($completeness2);

        $this->entityManager
            ->expects($this->at(2))
            ->method('remove')
            ->with($completeness3);

        $this->calculator->schedule($product);
    }

    /**
     * Method to get channel entity from a code
     *
     * @param string $channelCode
     *
     * @return Channel
     *
     * @throws \Exception
     */
    private function getChannel($channelCode)
    {
        if ($channelCode === self::CHANNEL_1) {
            return $this->channel1;
        } elseif ($channelCode === self::CHANNEL_2) {
            return $this->channel2;
        } else {
            throw new \Exception(sprintf('Unknown channel code in %s', get_class($this)));
        }
    }

    /**
     * Dynamically mock attribute requirements repository
     * @param Channel $channel
     */
    protected function mockRepository(Channel $channel)
    {
        $requirementsList = array(
            $this->createAttributeRequirement($this->attribute1, $channel),
            $this->createAttributeRequirement($this->attribute2, $channel)
        );

        $this
            ->repository
            ->expects($this->any())
            ->method('findBy')
            ->with(array('channel' => $channel, 'family' => $this->family, 'required' => true))
            ->will($this->returnValue($requirementsList));
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
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product', array('getValue'));

        $product
            ->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($values));

        $product->setFamily($this->family);

        return $product;
    }

    /**
     * initialize attribute requirements repository mock
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function initializeRepository()
    {
        $this->repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                                 ->disableOriginalConstructor()
                                 ->getMock();
    }

    /**
     * Initialize channels
     */
    protected function initializeChannels()
    {
        $this->channel1 = $this->createChannel(self::CHANNEL_1);
        $this->channel2 = $this->createChannel(self::CHANNEL_2);
    }

    /**
     * Initialize locales
     */
    protected function initializeLocales()
    {
        $this->locale1 = $this->createLocale(self::LOCALE_1);
        $this->locale2 = $this->createLocale(self::LOCALE_2);
    }

    /**
     * Initialize attributes
     */
    protected function initializeAttributes()
    {
        $this->attribute1 = $this->createAttribute(self::ATTR_1);
        $this->attribute2 = $this->createAttribute(self::ATTR_2);
    }

    /**
     * Create channel manager mock
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\ChannelManager
     */
    protected function createChannelManager()
    {
        $channelManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
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
     * @return \Pim\Bundle\CatalogBundle\Tests\Unit\Calculator\Channel
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
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function createLocaleManager()
    {
        $localeManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
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
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
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

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('PimCatalogBundle:AttributeRequirement')
            ->will($this->returnValue($this->repository));

        return $entityManager;
    }

    /**
     * Create attribute requirement entity
     *
     * @param ProductAttribute $attribute
     * @param Channel          $channel
     * @param boolean          $required
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeRequirement
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
     * Initialize validator mock
     */
    protected function initializeValidator()
    {
        $this->validator = $this->getMockBuilder('Symfony\Component\Validator\Validator')
                          ->disableOriginalConstructor()
                          ->getMock();
    }

    /**
     * Create attribute entity
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    protected function createAttribute($code)
    {
        $attribute = new ProductAttribute();
        $attribute->setCode($code);

        return $attribute;
    }

    protected function getCompletenessMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Completeness');
    }
}
