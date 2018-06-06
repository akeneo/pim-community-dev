<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Update the family variant properties
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantUpdater implements ObjectUpdaterInterface
{
    /** @var SimpleFactoryInterface */
    private $attributeSetFactory;

    /** @var TranslatableUpdater */
    private $translationUpdater;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /**
     * @param SimpleFactoryInterface                $attributeSetFactory
     * @param TranslatableUpdater                   $translationUpdater
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     */
    public function __construct(
        SimpleFactoryInterface $attributeSetFactory,
        TranslatableUpdater $translationUpdater,
        IdentifiableObjectRepositoryInterface $familyRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->translationUpdater = $translationUpdater;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($familyVariant, array $data, array $options = []): ObjectUpdaterInterface
    {
        if (!$familyVariant instanceof FamilyVariantInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($familyVariant),
                FamilyVariantInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($familyVariant, $field, $value);
        }

        return $this;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     * @param string                 $field
     * @param mixed                  $value
     */
    private function setData(FamilyVariantInterface $familyVariant, string $field, $value): void
    {
        switch ($field) {
            case 'code':
                if (!is_string($value)) {
                    throw InvalidPropertyTypeException::stringExpected($field, static::class, $value);
                }

                $familyVariant->setCode($value);
                break;
            case 'labels':
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                }

                foreach ($value as $label) {
                    if (null !== $label && !is_scalar($label)) {
                        throw InvalidPropertyTypeException::validArrayStructureExpected(
                            $field,
                            sprintf('one of the %s is not a scalar', $field),
                            static::class,
                            $label
                        );
                    }
                }

                $this->translationUpdater->update($familyVariant, $value);
                break;
            case 'family':
                if (null === $family = $this->familyRepository->findOneByIdentifier($value)) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'family',
                        'family code',
                        'The family does not exist',
                        static::class,
                        $value
                    );
                }

                $familyVariant->setFamily($family);
                break;
            case 'variant_attribute_sets':
                $isNew = null === $familyVariant->getId();

                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::arrayOfObjectsExpected($field, static::class, $value);
                }

                if (!$isNew &&
                    $familyVariant->getNumberOfLevel() < $this->getNumberOfLevel($value)
                ) {
                    throw new ImmutablePropertyException(
                        'variant_attribute_sets',
                        $this->getNumberOfLevel($value),
                        static::class,
                        'The number of variant attribute sets cannot be changed.'
                    );
                }

                foreach ($value as $attributeSetData) {
                    if (!is_array($attributeSetData)) {
                        throw InvalidPropertyTypeException::arrayOfObjectsExpected(
                            $field,
                            static::class,
                            $attributeSetData
                        );
                    }
                    if (!isset($attributeSetData['level'])) {
                        continue;
                    }

                    if (isset($attributeSetData['axes']) && !is_array($attributeSetData['axes'])) {
                        throw InvalidPropertyTypeException::arrayExpected(
                            sprintf('%s" in the property "%s', 'axes', $field),
                            static::class,
                            $attributeSetData['axes']
                        );
                    }

                    if (isset($attributeSetData['attributes']) && !is_array($attributeSetData['attributes'])) {
                        throw InvalidPropertyTypeException::arrayExpected(
                            sprintf('%s" in the property "%s', 'attributes', $field),
                            static::class,
                            $attributeSetData['attributes']
                        );
                    }

                    if (!is_integer($attributeSetData['level'])) {
                        throw InvalidPropertyTypeException::integerExpected(
                            sprintf('%s" in the property "%s', 'level', $field),
                            static::class,
                            $attributeSetData['level']
                        );
                    }

                    if (null === $attributeSet = $familyVariant->getVariantAttributeSet($attributeSetData['level'])) {
                        $attributeSet = $this->attributeSetFactory->create();
                        $attributeSet->setLevel($attributeSetData['level']);

                        $familyVariant->addVariantAttributeSet($attributeSet);
                    }

                    if (isset($attributeSetData['axes'])) {
                        $attributeSet->setAxes(
                            $this->getAttributes($attributeSetData['axes'], $attributeSetData['level'])
                        );
                    }

                    if (isset($attributeSetData['attributes'])) {
                        $attributeSet->setAttributes(
                            $this->getAttributes($attributeSetData['attributes'], $attributeSetData['level'])
                        );
                    }
                }
                break;
            default:
                throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param array $attributeSets
     *
     * @return int
     */
    private function getNumberOfLevel(array $attributeSets): int
    {
        $numberOfLevel = 0;
        foreach ($attributeSets as $attributeSet) {
            if ($numberOfLevel < $attributeSet['level']) {
                $numberOfLevel = $attributeSet['level'];
            }
        }

        return $numberOfLevel;
    }

    /**
     * @param string[] $attributeCodes
     * @param int      $level
     *
     * @return AttributeInterface[]
     */
    private function getAttributes(array $attributeCodes, int $level): array
    {
        return array_map(function ($attributeCode) use ($level) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null === $attribute) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    sprintf('attribute_set_%d', $level),
                    'attribute code',
                    'The attribute does not exist',
                    static::class,
                    $attributeCode
                );
            }

            return $attribute;
        }, $attributeCodes);
    }
}
