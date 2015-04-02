<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Exception\BusinessValidationException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Updates and validates an attribute option
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
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ValidatorInterface           $validator
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository, ValidatorInterface $validator)
    {
        $this->attributeRepository = $attributeRepository;
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
     * @throws BusinessValidationException
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

        // TODO: ugly fix to workaround issue with "attribute.group.code: This value should not be blank."
        // in case of existing option, attribute is a proxy, attribute group too, the validated group code is null
        ($attributeOption->getAttribute() !== null) ? $attributeOption->getAttribute()->getGroup()->getCode() : null;

        $isNew = $attributeOption->getId() === null;
        $readOnlyFields = ['attribute', 'code'];
        $updateViolations = new ConstraintViolationList();
        foreach ($data as $field => $data) {
            $isReadOnlyField = in_array($field, $readOnlyFields);
            if ($isNew || !$isReadOnlyField) {
                try {
                    $this->setData($attributeOption, $field, $data);
                } catch (\InvalidArgumentException $e) {
                    $setViolation = new ConstraintViolation(
                        $e->getMessage(),
                        $e->getMessage(),
                        [],
                        $attributeOption,
                        null,
                        null
                    );
                    $updateViolations->add($setViolation);
                }
            }
        }

        $validatorViolations = $this->validator->validate($attributeOption);
        $updateViolations->addAll($validatorViolations);

        if ($updateViolations->count() > 0) {
            throw new BusinessValidationException($updateViolations);
        }

        return $this;
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @param string                   $field
     * @param mixed                    $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(AttributeOptionInterface $attributeOption, $field, $data)
    {
        if ('code' === $field) {
            $attributeOption->setCode($data);
        }

        if ('attribute' === $field) {
            $attribute = $this->findAttribute($data);
            if (null !== $attribute) {
                $attributeOption->setAttribute($attribute);
            } else {
                throw new \InvalidArgumentException(sprintf('Attribute "%s" does not exist', $data));
            }
        }

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
    protected function findAttribute($code)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        return $attribute;
    }
}
