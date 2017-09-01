<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;

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
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                }

                if (null !== $familyVariant->getId() &&
                    $familyVariant->getNumberOfLevel() < $this->getNumberOfLevel($value)
                ) {
                    throw ImmutablePropertyException::immutableProperty(
                        'number of attribute sets',
                        sprintf('%d attribute sets', count($value)),
                        static::class
                    );
                }

                foreach ($value as $attributeSetData) {
                    if (!isset($attributeSetData['level'])) {
                        continue;
                    }

                    if (isset($attributeSetData['axes']) && !is_array($attributeSetData['axes'])) {
                        throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                    }

                    if (isset($attributeSetData['attributes']) && !is_array($attributeSetData['attributes'])) {
                        throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                    }

                    if (!is_numeric($attributeSetData['level'])) {
                        throw InvalidPropertyTypeException::numericExpected($field, static::class, $value);
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
                        if (null !== $familyVariant->getId()) {
                            $this->removeAttributeFromPreviousLevel($familyVariant, $attributeSetData);
                        }

                        $attributeSet->setAttributes(
                            $this->getAttributes($attributeSetData['attributes'], $attributeSetData['level'])
                        );
                    }
                }
                break;
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
     * We consider that if an attribute added in a given attribute set is also
     * present in an upper one: for instance in level 2 and level 1, then it
     * means this attribute has been moved from level 1 to level 2, and need to
     * be removed from level 1.
     *
     * We loop over all attribute sets preceding the current one (considering the
     * common attributes as a "level 0" attribute set), then we remove from them
     * all attributes that are already present in the current one.
     *
     * @param FamilyVariantInterface $familyVariant
     * @param array                  $attributeSetData
     */
    private function removeAttributeFromPreviousLevel(
        FamilyVariantInterface $familyVariant,
        array $attributeSetData
    ): void {
        $currentLevel = $attributeSetData['level'];
        $previousAttributeSetAttributes = [];

        while (1 < $currentLevel) {
            $attributeSet = $familyVariant->getVariantAttributeSet($attributeSetData['level'] - 1);
            if ($attributeSet instanceof VariantAttributeSetInterface) {
                $previousAttributeSetAttributes[] = $attributeSet->getAttributes();
            }
            $currentLevel--;
        }

        $previousAttributeSetAttributes[] = $familyVariant->getCommonAttributes();

        foreach ($previousAttributeSetAttributes as $previousAttributes) {
            foreach ($previousAttributes as $previousAttribute) {
                if (in_array($previousAttribute->getCode(), $attributeSetData['attributes'])) {
                    $previousAttributes->removeElement($previousAttribute);
                }
            }
        }
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
