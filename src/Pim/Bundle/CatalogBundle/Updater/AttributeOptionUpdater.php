<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Updates an attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
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
    public function update(AttributeOptionInterface $attributeOption, array $data, array $options = [])
    {
        $optionResolver = $this->createOptionsResolver();
        $resolvedData = $optionResolver->resolve($data);

        $isNew = $attributeOption->getId() === null;
        $readOnlyFields = ['attribute', 'code'];
        foreach ($resolvedData as $field => $data) {
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

        if ('labels' === $field) {
            foreach ($data as $localeCode => $label) {
                // TODO check the locale or we consider it's a domain validator concern?
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
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        return $attribute;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();

        $required = ['code', 'attribute', 'sort_order', 'labels'];
        $defaults = ['sort_order' => 1];
        $allowedTypes = [
            'code' => 'string',
            'attribute' => 'string',
            'sort_order' => 'int',
            'labels' => 'array'
        ];

        $resolver->setRequired($required);
        $resolver->setDefaults($defaults);
        $resolver->setAllowedTypes($allowedTypes);
        $integerNormalizer = function ($options, $value) {
            return (int) $value;
        };
        $resolver->setNormalizers(['sort_order' => $integerNormalizer]);

        return $resolver;
    }
}
