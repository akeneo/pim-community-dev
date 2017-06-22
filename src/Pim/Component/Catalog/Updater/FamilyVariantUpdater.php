<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeSetInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;

/**
 * Update the family variant properties's
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

    /**
     * @param SimpleFactoryInterface                $attributeSetFactory
     * @param TranslatableUpdater                   $translationUpdater
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     */
    public function __construct(
        SimpleFactoryInterface $attributeSetFactory,
        TranslatableUpdater $translationUpdater,
        IdentifiableObjectRepositoryInterface $familyRepository
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->translationUpdater = $translationUpdater;
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($familyVariant, array $data, array $options = [])
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

        $this->addCommonVariantAttributeSet($familyVariant, $data['variant-attribute-sets']);

        return $this;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     * @param string                 $field
     * @param mixed                  $value
     */
    private function setData(FamilyVariantInterface $familyVariant, $field, $value)
    {
        switch ($field) {
            case 'code':
                $familyVariant->setCode($value);
                break;
            case 'label':
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
            case 'variant-attribute-sets':
                foreach ($value as $key => $attributeSetData) {
                    /** @var AttributeSetInterface $attributeSet */
                    $attributeSet = $this->attributeSetFactory->create();
                    $attributeSet->setAxes($attributeSetData['axes']);
                    $attributeSet->setAttributes($attributeSetData['attributes']);

                    $familyVariant->addVariantAttributeSet($key + 1, $attributeSet);
                }
                break;
        }
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     * @param array                  $attributes
     */
    private function addCommonVariantAttributeSet(FamilyVariantInterface $familyVariant, array $attributes)
    {
        $attributes = call_user_func_array('array_merge', array_column($attributes, 'attributes'));
        $commonAttributes = array_diff($familyVariant->getFamily()->getAttributeCodes(), $attributes);

        /** @var AttributeSetInterface $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setAttributes($commonAttributes);

        $familyVariant->addCommonAttributeSet($attributeSet);
    }
}
