<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Validates an imported product
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductImportValidator extends ImportValidator
{
    /**
     * @var ConstraintGuesserInterface
     */
    protected $constraintGuesser;

    /**
     * @var array
     */
    protected $constraints = array();

    /**
     *
     * @param ValidatorInterface         $validator
     * @param TranslatorInterface        $translator
     * @param ConstraintGuesserInterface $constraintGuesser
     */
    public function __construct(
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        ConstraintGuesserInterface $constraintGuesser
    ) {
        parent::__construct($validator, $translator);
        $this->constraintGuesser = $constraintGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, array $columnsInfo, array $data, array $errors = array())
    {
        $identifier = $this->getIdentifier($columnsInfo, $entity);
        $this->checkIdentifier(get_class($entity), $identifier, $data);

        foreach ($columnsInfo as $columnInfo) {
            if (isset($columnInfo['attribute'])) {
                $violations = $this->validateProductValue($entity, $columnInfo);
            } else {
                $violations = $this->validator->validateProperty($entity, $columnInfo['propertyPath']);
            }

            if ($violations->count()) {
                $errors[$columnInfo['label']] = $this->getError($violations);
            }
        }

        return $errors;
    }

    /**
     * Validates a ProductValue
     *
     * @param ProductInterface $product
     * @param ProductAttribute $attribute
     *
     * @return ConstraintViolationListInterface
     */
    public function validateProductValue(ProductInterface $product, array $columnInfo)
    {
        return $this->validator->validateValue(
                $this->getProductValue($product, $columnInfo)->getData(),
                $this->getAttributeConstraints($columnInfo['attribute'])
        );
    }

    /**
     * Returns an array of constraints for a given attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return string
     */
    public function getAttributeConstraints(ProductAttribute $attribute)
    {
        $code = $attribute->getCode();
        if (!isset($this->constraints[$code])) {
            if ($this->constraintGuesser->supportAttribute($attribute)) {
                $this->constraints[$code] = $this->constraintGuesser->guessConstraints($attribute);
            } else {
                $this->constraints[$code] = array();
            }
        }

        return $this->constraints[$code];
    }

    protected function getIdentifier(array $columnsInfo, $entity)
    {
        foreach ($columnsInfo as $columnInfo) {
            if (isset($columnInfo['attribute']) &&
                AttributeCache::IDENTIFIER_ATTRIBUTE_TYPE === $columnInfo['attribute']->getAttributeType()) {
                return $this->getProductValue($entity, $columnInfo)->getData();
            }
        }
    }

    protected function getProductValue(ProductInterface $product, array $columnInfo)
    {
        return $product->getValue($columnInfo['name'], $columnInfo['locale'], $columnInfo['scope']);
    }
}
