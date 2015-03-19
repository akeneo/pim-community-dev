<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

/**
 * Updates an attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionUpdater implements AttributeOptionUpdaterInterface
{
    /** @staticvar string */
    const OPTION_CODE_FIELD = 'code';

    /** @staticvar string */
    const ATTRIBUTE_CODE_FIELD = 'attribute';

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $repository
     */
    public function __construct(AttributeRepositoryInterface $repository)
    {
        $this->attributeRepository = $repository;
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
    public function update(AttributeOptionInterface $attributeOption, $data, array $options = [])
    {
        // TODO: option resolver

        $isNew = $attributeOption->getId() === null;
        $readOnlyFields = [self::ATTRIBUTE_CODE_FIELD, self::OPTION_CODE_FIELD];
        foreach ($data as $field => $data) {
            $isReadOnlyField = in_array($field, $readOnlyFields);
            if ($isNew) {
                $this->setData($attributeOption, $field, $data);
            } elseif (false === $isReadOnlyField) {
                $this->setData($attributeOption, $field, $data);
            }
        }

        return $this;
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @param string                   $field
     * @param mixed                    $data
     */
    protected function setData(AttributeOptionInterface $attributeOption, $field, $data)
    {
        // TODO: option resolver!
        $supportedFields = ['code', 'sort_order', 'labels', 'attribute'];
        if (!in_array($field, $supportedFields)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Field "%s" is not supported, the updater supports [%s]',
                    $field,
                    implode(', ', $supportedFields)
                )
            );
        }

        if ('code' === $field) {
            $attributeOption->setCode($data);
        }

        if ('attribute' === $field) {
            $attribute = $this->getAttribute($data);
            if (null === $attribute) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Attribute "%s" does not exists',
                        $field
                    )
                );
            }
            $attributeOption->setAttribute($attribute);
        }

        // TODO: label + locale code as option?
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
    protected function getAttribute($code)
    {
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);

        return $attribute;
    }
}
