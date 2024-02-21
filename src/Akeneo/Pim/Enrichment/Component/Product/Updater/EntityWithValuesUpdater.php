<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Update values of an entity with values.
 *
 * Note that this updater acts simply by putting values into the entity, whatever
 * theses values should be in this entity or not.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithValuesUpdater implements ObjectUpdaterInterface
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
    public function update($entityWithValues, array $values, array $options = [])
    {
        if (!$entityWithValues instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($entityWithValues),
                EntityWithValuesInterface::class
            );
        }

        $this->checkValuesData($values);
        $this->updateEntityWithValues($entityWithValues, $values);

        return $this;
    }

    /**
     * Update values of an entity with values
     *
     * @param EntityWithValuesInterface $entityWithValues
     * @param array                     $values
     */
    protected function updateEntityWithValues(EntityWithValuesInterface $entityWithValues, array $values)
    {
        foreach ($values as $code => $value) {
            foreach ($value as $data) {
                $options = ['locale' => $data['locale'], 'scope' => $data['scope']];
                $this->propertySetter->setData($entityWithValues, $code, $data['data'], $options);
            }
        }
    }

    /**
     * Check the structure of the given entity with values.
     *
     * @param mixed $entityWithValues
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkValuesData($entityWithValues)
    {
        if (!is_array($entityWithValues)) {
            throw InvalidPropertyTypeException::arrayExpected('values', static::class, $entityWithValues);
        }

        foreach ($entityWithValues as $code => $values) {
            if (!is_array($values)) {
                throw InvalidPropertyTypeException::arrayExpected($code, static::class, $values);
            }

            foreach ($values as $value) {
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $code,
                        'one of the values is not an array',
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
