<?php

namespace Pim\Bundle\CatalogBundle\Validator\Mapping;

use Symfony\Component\Validator\MetadataFactoryInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Create a ClassMetadata instance for an AbstractProductValue instance
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
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        if (!$value instanceof AbstractProductValue) {
            throw new NoSuchMetadataException();
        }

        $class = ClassUtils::getClass($value);

        $metadata = $this->factory->createMetadata($class);

        $attribute = $value->getAttribute();
        foreach ($this->guesser->guessConstraints($attribute) as $constraint) {
            $metadata->addPropertyConstraint($attribute->getBackendType(), $constraint);
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        if ($value instanceof AbstractProductValue) {
            return true;
        }

        return false;
    }
}
