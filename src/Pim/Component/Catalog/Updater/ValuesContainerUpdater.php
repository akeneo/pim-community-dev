<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ValuesContainerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValuesContainerUpdater implements ObjectUpdaterInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /**
     * @param PropertySetterInterface $propertySetter
     */
    public function __construct(PropertySetterInterface $propertySetter)
    {
        $this->propertySetter = $propertySetter;
    }

    /**
     * {@inheritdoc}
     */
    public function update($valuesContainer, array $values, array $options = [])
    {
        if (!$valuesContainer instanceof ValuesContainerInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($valuesContainer),
                ValuesContainerInterface::class
            );
        }

        $this->checkValuesData($values);
        $this->updateValuesContainer($valuesContainer, $values);

        return $this;
    }

    /**
     * Sets the values of the values container,
     *
     * @param ValuesContainerInterface $valuesContainer
     * @param array                    $values
     */
    protected function updateValuesContainer(ValuesContainerInterface $valuesContainer, array $values)
    {
        foreach ($values as $code => $value) {
            foreach ($value as $data) {
                $hasValue = $valuesContainer->getValue($code, $data['locale'], $data['scope']);
                $providedData = ('' === $data['data'] || [] === $data['data'] || null === $data['data']) ? false : true;

                if ($providedData || $hasValue) {
                    $options = ['locale' => $data['locale'], 'scope' => $data['scope']];
                    $this->propertySetter->setData($valuesContainer, $code, $data['data'], $options);
                }
            }
        }
    }

    /**
     * Check the structure of the values container.
     *
     * @param mixed $valuesContainer
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkValuesData($valuesContainer)
    {
        if (!is_array($valuesContainer)) {
            throw InvalidPropertyTypeException::arrayExpected('values', static::class, $valuesContainer);
        }

        foreach ($valuesContainer as $code => $values) {
            if (!is_array($values)) {
                throw InvalidPropertyTypeException::arrayExpected($code, static::class, $values);
            }

            foreach ($values as $value) {
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $code,
                        'one of the values is not an array.',
                        static::class,
                        $values
                    );
                }

                if (!array_key_exists('locale', $value)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'locale', static::class, $value);
                }

                if (!array_key_exists('scope', $value)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'scope', static::class, $value);
                }

                if (!array_key_exists('data', $value)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'data', static::class, $value);
                }

                if (null !== $value['locale'] && !is_string($value['locale'])) {
                    $message = 'Property "%s" expects a value with a string as locale, "%s" given.';

                    throw new InvalidPropertyTypeException(
                        $code,
                        $value['locale'],
                        static::class,
                        sprintf($message, $code, gettype($value['locale'])),
                        InvalidPropertyTypeException::STRING_EXPECTED_CODE
                    );
                }

                if (null !== $value['scope'] && !is_string($value['scope'])) {
                    $message = 'Property "%s" expects a value with a string as scope, "%s" given.';

                    throw new InvalidPropertyTypeException(
                        $code,
                        $value['scope'],
                        static::class,
                        sprintf($message, $code, gettype($value['scope'])),
                        InvalidPropertyTypeException::STRING_EXPECTED_CODE
                    );
                }
            }
        }
    }
}
