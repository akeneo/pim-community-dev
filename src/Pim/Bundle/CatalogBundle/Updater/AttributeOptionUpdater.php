<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Exception\UpdaterException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Updates an attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionUpdater implements UpdaterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param AttributeRepositoryInterface $repository
     * @param ValidatorInterface           $validator
     */
    public function __construct(AttributeRepositoryInterface $repository, ValidatorInterface $validator)
    {
        $this->attributeRepository = $repository;
        $this->validator           = $validator;
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
     *
     * @throws \InvalidArgumentException
     */
    public function update($attributeOption, array $data, array $options = [])
    {
        if (!$attributeOption instanceof AttributeOptionInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface", "%s" provided.',
                    ClassUtils::getClass($attributeOption)
                )
            );
        }

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

        $violations = $this->validator->validate($attributeOption);
        if ($violations->count() !== 0) {
            throw new UpdaterException($violations);
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
