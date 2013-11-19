<?php

namespace Oro\Bundle\FormBundle\Form\Extension;

use Oro\Bundle\FormBundle\Validator\ConstraintFactory;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

class ConstraintAsOptionExtension extends AbstractTypeExtension
{
    /**
     * @var ConstraintFactory
     */
    protected $constraintFactory;

    /**
     * @param ConstraintFactory $constraintFactory
     */
    public function __construct(ConstraintFactory $constraintFactory)
    {
        $this->constraintFactory = $constraintFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $constraintsNormalizer = function (Options $options, $constraints) {
            $constraints = is_object($constraints) ? array($constraints) : (array) $constraints;

            $constraintObjects = array();
            foreach ($constraints as $constraint) {
                if (is_array($constraint)) {
                    foreach ($constraint as $name => $options) {
                        $constraintObjects[] = $this->constraintFactory->create($name, $options);
                    }
                } elseif (is_object($constraint)) {
                    $constraintObjects[] = $constraint;
                }
            }

            return $constraintObjects;
        };

        $resolver->setNormalizers(array('constraints' => $constraintsNormalizer));
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
