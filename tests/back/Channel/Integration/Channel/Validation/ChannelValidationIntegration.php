<?php

namespace AkeneoTest\Pim\Channel\Integration\Channel\Validation;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ChannelValidationIntegration extends TestCase
{
    public function testChannelUniqueEntity()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            ['code' => 'ecommerce', 'category_tree' => 'master', 'currencies' => ['EUR'], 'locales' => ['fr_FR']]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This value is already used.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testChannelImmutableCode()
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $this->getUpdater()->update($channel, ['code' => 'mobile']);

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testChannelCodeNotBlank()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            ['category_tree' => 'master', 'currencies' => ['EUR'], 'locales' => ['fr_FR']]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value should not be blank.',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testChannelCategoryNotBlank()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            ['code' => 'mobile', 'currencies' => ['EUR'], 'locales' => ['fr_FR']]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value should not be blank.',
            $violation->getMessage()
        );
        $this->assertSame('category', $violation->getPropertyPath());
    }

    public function testChannelCategoryNotRoot()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            ['code' => 'mobile', 'currencies' => ['EUR'], 'locales' => ['fr_FR'], 'category_tree' => 'categoryA']
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The category "categoryA" has to be a root category.',
            $violation->getMessage()
        );
        $this->assertSame('category', $violation->getPropertyPath());
    }

    public function testChannelCodeRegex()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'          => 'new-channel',
                'category_tree' => 'master',
                'currencies'    => ['EUR'],
                'locales'       => ['fr_FR'],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'Channel code may contain only letters, numbers and underscores',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testChannelCodeLength()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'          => str_pad('longCode', 101, "l"),
                'category_tree' => 'master',
                'currencies'    => ['EUR'],
                'locales'       => ['fr_FR'],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value is too long. It should have 100 characters or less.',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testChannelCurrenciesCollectionLength()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'          => 'new_channel',
                'category_tree' => 'master',
                'locales'       => ['fr_FR'],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This collection should contain 1 element or more.',
            $violation->getMessage()
        );
        $this->assertSame('currencies', $violation->getPropertyPath());
    }

    public function testChannelCurrenciesNotActivated()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'          => 'new_channel',
                'category_tree' => 'master',
                'currencies'    => ['BEC'],
                'locales'       => ['fr_FR'],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The currency "BEC" has to be activated.',
            $violation->getMessage()
        );
        $this->assertSame('currencies[0]', $violation->getPropertyPath());
    }


    public function testChannelLocalesCollectionLength()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'          => 'new_channel',
                'category_tree' => 'master',
                'currencies'    => ['EUR'],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This collection should contain 1 element or more.',
            $violation->getMessage()
        );
        $this->assertSame('locales', $violation->getPropertyPath());
    }

    public function testChannelConversionUnitsInvalidAttributeCode()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'             => 'new_channel',
                'category_tree'    => 'master',
                'currencies'       => ['EUR'],
                'locales'          => ['fr_FR'],
                'conversion_units' => [
                    'attr'   => 'KILOGRAM',
                ],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The attribute "attr" does not exist.',
            $violation->getMessage()
        );
        $this->assertSame('conversionUnits', $violation->getPropertyPath());
    }

    public function testChannelConversionUnitsNotAMetricAttribute()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'             => 'new_channel',
                'category_tree'    => 'master',
                'currencies'       => ['EUR'],
                'locales'          => ['fr_FR'],
                'conversion_units' => [
                    'a_price'   => 'KILOGRAM',
                ],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The attribute "a_price" is not a metric attribute.',
            $violation->getMessage()
        );
        $this->assertSame('conversionUnits', $violation->getPropertyPath());
    }

    public function testChannelConversionUnitsInvalidUnitCode()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'             => 'new_channel',
                'category_tree'    => 'master',
                'currencies'       => ['EUR'],
                'locales'          => ['fr_FR'],
                'conversion_units' => [
                    'a_metric_without_decimal'   => 'KILOWATT',
                ],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The unit "KILOWATT" does not exist or does not belong to the default metric family of the given attribute "a_metric_without_decimal".',
            $violation->getMessage()
        );
        $this->assertSame('conversionUnits', $violation->getPropertyPath());
    }

    public function testChannelTranslationsLength()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'          => 'new_channel',
                'category_tree' => 'master',
                'currencies'    => ['EUR'],
                'locales'       => ['fr_FR'],
                'labels'        => [
                    'en_US' => str_pad('long_label', 101, "_"),
                ],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value is too long. It should have 100 characters or less.',
            $violation->getMessage()
        );
        $this->assertSame('translations[0].label', $violation->getPropertyPath());
    }

    public function testChannelTranslationsLocale()
    {
        $channel = $this->createChannel();
        $this->getUpdater()->update(
            $channel,
            [
                'code'          => 'new_channel',
                'category_tree' => 'master',
                'currencies'    => ['EUR'],
                'locales'       => ['fr_FR'],
                'labels'        => [
                    'en_FR' => 'Attribute group',
                ],
            ]
        );

        $violations = $this->getValidator()->validate($channel);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The locale "en_FR" does not exist.',
            $violation->getMessage()
        );
        $this->assertSame('translations[0].locale', $violation->getPropertyPath());
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->get('validator');
    }

    /**
     * @return \Akeneo\Channel\Component\Model\ChannelInterface
     */
    protected function createChannel()
    {
        return $this->get('pim_catalog.factory.channel')->create();
    }

    /**
     * @return ObjectUpdaterInterface
     */
    protected function getUpdater()
    {
        return $this->get('pim_catalog.updater.channel');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
