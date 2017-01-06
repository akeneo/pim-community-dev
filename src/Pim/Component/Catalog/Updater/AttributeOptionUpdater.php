<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Updates and validates an attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': 2,
     *     'labels': {
     *         'de_DE': '210 x 1219 mm',
     *         'en_US': '210 x 1219 mm',
     *         'fr_FR': '210 x 1219 mm'
     *     }
     * }
     */
    public function update($attributeOption, array $data, array $options = [])
    {
        if (!$attributeOption instanceof AttributeOptionInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($attributeOption),
                'Pim\Component\Catalog\Model\AttributeOptionInterface'
            );
        }

        $isNew = $attributeOption->getId() === null;
        $readOnlyFields = ['attribute', 'code'];
        foreach ($data as $field => $data) {
            $isReadOnlyField = in_array($field, $readOnlyFields);
            if ($isNew || !$isReadOnlyField) {
                $this->setData($attributeOption, $field, $data);
            }
        }

        return $this;
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @param string                   $field
     * @param mixed                    $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(AttributeOptionInterface $attributeOption, $field, $data)
    {
        if ('code' === $field) {
            $attributeOption->setCode($data);
        }

        if ('attribute' === $field) {
            $attribute = $this->findAttribute($data);
            if (null !== $attribute) {
                $attributeOption->setAttribute($attribute);
            } else {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attribute',
                    'attribute code',
                    'The attribute does not exist',
                    'updater',
                    'attribute option',
                    $data
                );
            }
        }

        if ('labels' === $field) {
            foreach ($data as $localeCode => $label) {
                $attributeOption->setLocale($localeCode);
                $translation = $attributeOption->getTranslation();
                $translation->setLabel($label);
            }
        }

        if ('sort_order' === $field) {
            $attributeOption->setSortOrder($data);
        }
    }

    /**
     * @param string $code
     *
     * @return AttributeInterface|null
     */
    protected function findAttribute($code)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        return $attribute;
    }
}
