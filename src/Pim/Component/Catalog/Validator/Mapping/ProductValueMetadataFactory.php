<?php

namespace Pim\Component\Catalog\Validator\Mapping;

use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

/**
 * Create a ClassMetadata instance for an ProductValueInterface instance
 * Constraints are guessed from the value's attribute
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueMetadataFactory implements MetadataFactoryInterface
{
    /** @var ConstraintGuesserInterface */
    protected $guesser;

    /** @var ClassMetadataFactory */
    protected $factory;

    /** @var array */
    protected $attrConstraintsCache;

    /**
     * Constructor
     *
     * @param ConstraintGuesserInterface $guesser
     * @param ClassMetadataFactory|null  $factory
     */
    public function __construct(ConstraintGuesserInterface $guesser, ClassMetadataFactory $factory = null)
    {
        $this->guesser = $guesser;
        $this->factory = $factory ?: new ClassMetadataFactory();
        $this->attrConstraintsCache = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        if (!$value instanceof ProductValueInterface) {
            throw new NoSuchMetadataException();
        }

        $metadata = $this->createMetadata($value);

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        if ($value instanceof ProductValueInterface) {
            return true;
        }

        return false;
    }

    /**
     * @param ProductValueInterface $value
     *
     * @return ClassMetadata
     */
    protected function createMetadata(ProductValueInterface $value)
    {
        $class = ClassUtils::getClass($value);
        $attribute = $value->getAttribute();
        $cacheKey = $attribute->getCode();
        if (!isset($this->attrConstraintsCache[$cacheKey])) {
            $metadata = $this->factory->createMetadata($class);
            foreach ($this->guesser->guessConstraints($attribute) as $constraint) {
                $this->addConstraint($metadata, $constraint, $attribute);
            }
            $this->attrConstraintsCache[$cacheKey] = $metadata;
        }

        return $this->attrConstraintsCache[$cacheKey];
    }

    /**
     * @param ClassMetadata      $metadata
     * @param Constraint         $constraint
     * @param AttributeInterface $attribute
     */
    protected function addConstraint(ClassMetadata $metadata, Constraint $constraint, AttributeInterface $attribute)
    {
        $target = $constraint->getTargets();
        if (is_array($target)) {
            throw new \LogicException('No support provided for constraint on many targets');
        } elseif (Constraint::PROPERTY_CONSTRAINT === $target) {
            $metadata->addPropertyConstraint('data', $constraint);
        } elseif (Constraint::CLASS_CONSTRAINT === $target) {
            $metadata->addConstraint($constraint);
        }
    }
}
