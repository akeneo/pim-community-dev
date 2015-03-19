<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

/**
 * Provides basic operations to update an attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionUpdater implements AttributeOptionUpdaterInterface
{
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
     *
     * $field: "code"
     * $data: "option_code"
     *
     * $field: "attribute"
     * $data: "attribute_code",
     *
     * $field: "labels"
     * $data: {
     *     "en_US": "My US Label",
     *     "fr_FR": "My FR Label"
     * }
     *
     * $field: "sort_order"
     * $data: 2
     */
    public function setData(AttributeOptionInterface $attributeOption, $field, $data, array $options = [])
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

        return $this;
    }

    /**
     * Fetch the attribute by its code
     *
     * @param string $code
     *
     * @throws \LogicException
     *
     * @return AttributeInterface|null
     */
    protected function getAttribute($code)
    {
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);

        return $attribute;
    }
}
