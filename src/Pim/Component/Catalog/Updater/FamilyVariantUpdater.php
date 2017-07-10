<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
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

                foreach ($value as $key => $attributeSetData) {
                    /** @var VariantAttributeSetInterface $attributeSet */
                    if (!isset($attributeSetData['axes']) || !isset($attributeSetData['attributes'])) {
                        continue;
                    }

                    if (!is_array($attributeSetData['axes'])) {
                        throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                    }

                    if (!is_array($attributeSetData['attributes'])) {
                        throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                    }

                    $attributeSet = $this->attributeSetFactory->create();
                    $attributeSet->setAxes($this->getAttributes($attributeSetData['axes']));
                    $attributeSet->setAttributes($this->getAttributes($attributeSetData['attributes']));

                    $familyVariant->addVariantAttributeSet($key + 1, $attributeSet);
                }
                break;
        }
    }

    /**
     * @param array $attributeCodes
     *
     * @return array
     */
    private function getAttributes(array $attributeCodes): array
    {
        $attributes = [];
        foreach ($attributeCodes as $attributeCode) {
            $attributes[] = $this->attributeRepository->findOneByIdentifier($attributeCode);
        }

        return $attributes;
    }
}
