<?php

namespace Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Updates a family.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyUpdater implements ObjectUpdaterInterface
{
    protected PropertyAccessorInterface $accessor;

    public function __construct(
        protected AttributeRepositoryInterface $attributeRepository,
        protected ChannelRepositoryInterface $channelRepository,
        protected AttributeRequirementFactory $attrRequiFactory,
        protected AttributeRequirementRepositoryInterface $requirementRepo,
        protected TranslatableUpdater $translatableUpdater,
        protected IdentifiableObjectRepositoryInterface $localeRepository,
    ) {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function update($family, array $data, array $options = [])
    {
        if (!$family instanceof FamilyInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($family),
                FamilyInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->validateDataType($field, $value);
            $this->setData($family, $field, $value);
        }

        return $this;
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
        if (in_array($field, ['code', 'attribute_as_label', 'attribute_as_image'])) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } elseif (in_array($field, ['attributes', 'labels'])) {
            $this->validateScalarArray($field, $data);
        } elseif ('attribute_requirements' === $field) {
            $this->validateAttributeRequirements($data);
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * Validate that it is an array with scalar values.
     *
     * @param string $field
     * @param mixed $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function validateScalarArray($field, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
        }

        foreach ($data as $value) {
            if (null !== $value && !is_scalar($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('one of the %s is not a scalar', $field),
                    static::class,
                    $data
                );
            }
        }
    }
    /**
     * @param mixed $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function validateAttributeRequirements($data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected('attribute_requirements', static::class, $data);
        }
        foreach ($data as $channel => $attributes) {
            if (!is_array($attributes)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    'attribute_requirements',
                    sprintf('the channel "%s" is not an array', $channel),
                    static::class,
                    $data
                );
            }
            foreach ($attributes as $attribute) {
                if (null !== $attribute && !is_scalar($attribute)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        'attribute_requirements',
                        sprintf('one of the attributes in the channel "%s" is not a scalar', $channel),
                        static::class,
                        $data
                    );
                }
            }
        }
    }

    /**
     * @param FamilyInterface $family
     * @param string          $field
     * @param mixed           $data
     *
     * @throws UnknownPropertyException
     * @throws InvalidPropertyException
     */
    protected function setData(FamilyInterface $family, $field, $data)
    {
        switch ($field) {
            case 'labels':
                $this->setLabels($family, $data);
                break;
            case 'attribute_requirements':
                $this->setAttributeRequirements($family, $data);
                break;
            case 'attributes':
                $this->setAttributes($family, $data);
                break;
            case 'attribute_as_label':
                $this->setAttributeAsLabel($family, $data);
                break;
            case 'attribute_as_image':
                $this->setAttributeAsImage($family, $data);
                break;
            default:
                $this->setValue($family, $field, $data);
        }
    }

    /**
     * set labels on a family, ensuring correct case of locale codes.
     * @param array $localizedLabels
     */
    private function setLabels(FamilyInterface $family, array $localizedLabels): void
    {
        // using known locale code when found (modulo case-insensitive comparison)
        // leaving unknown locale code as is for the moment (Jira PIM-10372, )
        $normalizedLocalizedLabels = [];
        foreach ($localizedLabels as $localeCode => $label) {
            $knownLocale = $this->localeRepository->findOneByIdentifier($localeCode);
            $normalizedLocalCode = null === $knownLocale ? $localeCode : $knownLocale->getCode();
            $normalizedLocalizedLabels[$normalizedLocalCode] = $label;
        };

        $this->translatableUpdater->update($family, $normalizedLocalizedLabels);
    }

    /**
     * @param FamilyInterface $family
     * @param string          $field
     * @param mixed           $data
     *
     * @throws UnknownPropertyException
     */
    protected function setValue(FamilyInterface $family, string $field, $data)
    {
        try {
            $this->accessor->setValue($family, $field, $data);
        } catch (NoSuchPropertyException $e) {
            throw UnknownPropertyException::unknownProperty($field, $e);
        }
    }

    /**
     * Set the new attribute requirements.
     * If a channel is not present in the requirement list, this method does not update the requirements of this
     * channel.
     *
     * @param FamilyInterface $family
     * @param array           $newRequirements The requirements for each channel. For example:
     *                                         ['mobile' => ['attr1', 'attr2'], 'tabled' => ['attr3']]
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributeRequirements(FamilyInterface $family, array $newRequirements)
    {
        // when creating a family, the identifier attribute must be required
        if (null === $family->getCreated()) {
            $identifierCode = $this->attributeRepository->getIdentifierCode();
            foreach ($newRequirements as $channelCode => $requirements) {
                if (!\in_array($identifierCode, $requirements)) {
                    $newRequirements[$channelCode][] = $identifierCode;
                }
            }
        }
        foreach ($family->getAttributeRequirements() as $requirement) {
            $channelCode = $requirement->getChannelCode();
            if (array_key_exists($channelCode, $newRequirements)) {
                $attributeCode = $requirement->getAttributeCode();
                $key = array_search($attributeCode, $newRequirements[$channelCode], true);
                if (false === $key) {
                    $family->removeAttributeRequirement($requirement);
                } elseif (true === $requirement->isRequired()) {
                    unset($newRequirements[$channelCode][$key]);
                }
            }
        }

        foreach ($newRequirements as $channelCode => $requirements) {
            $createdRequirements = $this->createAttributeRequirementsByChannel($family, $requirements, $channelCode);
            foreach ($createdRequirements as $createdRequirement) {
                $createdRequirement->setRequired(true);
                $family->addAttributeRequirement($createdRequirement);
            }
        }
    }

    /**
     * @param FamilyInterface $family
     * @param array           $attributeCodes
     * @param string          $channelCode
     *
     * @throws InvalidPropertyException
     *
     * @return array
     */
    protected function createAttributeRequirementsByChannel(
        FamilyInterface $family,
        array $attributeCodes,
        $channelCode
    ) {
        $requirements = [];

        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'attribute_requirements',
                'code',
                'The channel does not exist',
                static::class,
                $channelCode
            );
        }

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null === $attribute) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attribute_requirements',
                    'code',
                    'The attribute does not exist',
                    static::class,
                    $attributeCode
                );
            }
            $requirements[] = $this->createAttributeRequirement($family, $attribute, $channel);
        }

        return $requirements;
    }

    /**
     * @param FamilyInterface    $family
     * @param AttributeInterface $attribute
     * @param ChannelInterface   $channel
     *
     * @throws InvalidPropertyException
     *
     * @return AttributeRequirementInterface
     */
    protected function createAttributeRequirement(
        FamilyInterface $family,
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $requirement = $this->requirementRepo->findOneBy(
            ['attribute' => $attribute, 'channel' => $channel, 'family' => $family]
        );

        if (null === $requirement) {
            $requirement = $this->attrRequiFactory->createAttributeRequirement($attribute, $channel, true);
        }

        return $requirement;
    }

    /**
     * @param FamilyInterface $family
     * @param array           $data
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributes(FamilyInterface $family, array $data)
    {
        // when creating a family, we always want to include the identifier attribute
        if (null === $family->getCreated()) {
            $identifierCode = $this->attributeRepository->getIdentifierCode();
            if (!\in_array($identifierCode, $data)) {
                $data[] = $identifierCode;
            }
        }

        $newAttributes = [];
        foreach ($data as $attributeCode) {
            if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                $newAttributes[] = $attribute;
            } else {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attributes',
                    'code',
                    'The attribute does not exist',
                    static::class,
                    $attributeCode
                );
            }
        }

        $family->updateAttributes($newAttributes);
    }

    /**
     * @param FamilyInterface $family
     * @param string          $data
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributeAsLabel(FamilyInterface $family, $data)
    {
        if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($data)) {
            $family->setAttributeAsLabel($attribute);
        } else {
            throw InvalidPropertyException::validEntityCodeExpected(
                'attribute_as_label',
                'code',
                'The attribute does not exist',
                static::class,
                $data
            );
        }
    }

    /**
     * @param FamilyInterface $family
     * @param string          $data
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributeAsImage(FamilyInterface $family, $data): void
    {
        if (null === $data || '' === $data) {
            $family->setAttributeAsImage(null);
        } elseif (null !== $attribute = $this->attributeRepository->findOneByIdentifier($data)) {
            $family->setAttributeAsImage($attribute);
        } else {
            throw InvalidPropertyException::validEntityCodeExpected(
                'attribute_as_image',
                'code',
                'The attribute does not exist',
                static::class,
                $data
            );
        }
    }
}
