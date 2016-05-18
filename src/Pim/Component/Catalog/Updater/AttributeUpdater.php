<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates an attribute.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $attrGroupRepo;

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var array */
    protected $referenceDataType;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param AttributeGroupRepositoryInterface $attrGroupRepo
     * @param array                             $referenceDataType
     * @param LocaleRepositoryInterface         $localeRepository
     * @param ConfigurationRegistryInterface    $registry
     */
    public function __construct(
        AttributeGroupRepositoryInterface $attrGroupRepo,
        array $referenceDataType,
        LocaleRepositoryInterface $localeRepository,
        ConfigurationRegistryInterface $registry = null
    ) {
        $this->attrGroupRepo     = $attrGroupRepo;
        $this->accessor          = PropertyAccess::createPropertyAccessor();
        $this->registry          = $registry;
        $this->referenceDataType = $referenceDataType;
        $this->localeRepository  = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($attribute, array $data, array $options = [])
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\AttributeInterface", "%s" provided.',
                    ClassUtils::getClass($attribute)
                )
            );
        }

        $this->checkIfReferenceDataExists($data);

        foreach ($data as $field => $value) {
            $this->setData($attribute, $field, $value);
        }

        return $this;
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $field
     * @param mixed              $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(AttributeInterface $attribute, $field, $data)
    {
        switch ($field) {
            case 'labels':
                $this->setLabels($attribute, $data);
                break;
            case 'group':
                $this->setGroup($attribute, $data);
                break;
            case 'available_locales':
                $this->setAvailableLocales($attribute, $field, $data);
                break;
            case 'date_min':
                $this->validateDateFormat($data);
                $date = $this->getDate($data);
                $attribute->setDateMin($date);
                break;
            case 'date_max':
                $this->validateDateFormat($data);
                $date = $this->getDate($data);
                $attribute->setDateMax($date);
                break;
            default:
                $this->accessor->setValue($attribute, $field, $data);
        }
    }

    /**
     * @param string $code
     *
     * @return AttributeGroupInterface|null
     */
    protected function findAttributeGroup($code)
    {
        $attributeGroup = $this->attrGroupRepo->findOneByIdentifier($code);

        return $attributeGroup;
    }

    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     */
    protected function checkIfReferenceDataExists($value)
    {
        if (isset($value['attributeType']) && in_array($value['attributeType'], $this->referenceDataType)) {
            if (!$this->registry->has($value['reference_data_name'])) {
                $references = array_keys($this->registry->all());
                throw new \InvalidArgumentException(
                    sprintf(
                        'Reference data "%s" does not exist. Allowed values are: %s',
                        $value['reference_data_name'],
                        implode(', ', $references)
                    )
                );
            }
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $data
     */
    protected function setLabels(AttributeInterface $attribute, array $data)
    {
        foreach ($data as $localeCode => $label) {
            $attribute->setLocale($localeCode);
            $translation = $attribute->getTranslation();
            $translation->setLabel($label);
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $field
     * @param array              $availableLocaleCodes
     */
    protected function setAvailableLocales(AttributeInterface $attribute, $field, array $availableLocaleCodes)
    {
        $locales = [];
        foreach ($availableLocaleCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null !== $locale) {
                $locales[] = $locale;
            }
        }

        $this->accessor->setValue($attribute, $field, $locales);
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setGroup(AttributeInterface $attribute, $data)
    {
        $attributeGroup = $this->findAttributeGroup($data);
        if (null !== $attributeGroup) {
            $attribute->setGroup($attributeGroup);
        } else {
            throw new \InvalidArgumentException(sprintf('AttributeGroup "%s" does not exist', $data));
        }
    }

    /**
     * @param string $data
     *
     * @throws \InvalidArgumentException
     */
    protected function validateDateFormat($data)
    {
        if (null === $data) {
            return;
        }

        if (!preg_match('/(\d{4})-(\d{2})-(\d{2})/', $data, $dateValues)) {
            throw new \InvalidArgumentException(
                sprintf('Attribute expects a string with the format "yyyy-mm-dd" as data, "%s" given', $data)
            );
        }

        if (!checkdate($dateValues[2], $dateValues[3], $dateValues[1])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid date, "%s" given', $data)
            );
        }
    }

    /**
     * @param string $date
     *
     * @return \DateTime|null
     */
    protected function getDate($date)
    {
        if (null === $date) {
            return null;
        }

        return new \DateTime($date);
    }
}
