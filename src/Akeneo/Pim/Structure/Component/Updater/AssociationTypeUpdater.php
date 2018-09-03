<?php

namespace Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates an association type.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeUpdater implements ObjectUpdaterInterface
{
    /** @var TranslatableUpdater */
    protected $translatableUpdater;

    /**
     * @param TranslatableUpdater $translatableUpdater
     */
    public function __construct(TranslatableUpdater $translatableUpdater)
    {
        $this->translatableUpdater = $translatableUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function update($associationType, array $data, array $options = [])
    {
        if (!$associationType instanceof AssociationTypeInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($associationType),
                AssociationTypeInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->validateDataType($field, $value);
            $this->setData($associationType, $field, $value);
        }

        return $this;
    }

    /**
     * @param AssociationTypeInterface $associationType
     * @param string                   $field
     * @param mixed                    $data
     *
     * @throws UnknownPropertyException
     */
    protected function setData(AssociationTypeInterface $associationType, $field, $data)
    {
        if ('code' === $field) {
            $associationType->setCode($data);
        } elseif ('labels' === $field) {
            $this->translatableUpdater->update($associationType, $data);
        }
    }

    /**
     * Validate the data type of a field.
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     * @throws UnknownPropertyException
     */
    protected function validateDataType($field, $data)
    {
        if ('labels' === $field) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
            }

            foreach ($data as $value) {
                if (null !== $value && !is_scalar($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('one of the "%s" values is not a scalar', $field),
                        static::class,
                        $data
                    );
                }
            }
        } elseif ('code' === $field) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }
}
