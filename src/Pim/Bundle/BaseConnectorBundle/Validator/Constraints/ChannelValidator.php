<?php

namespace Pim\Bundle\BaseConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Validate that the selected channel exists
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelValidator extends ChoiceValidator
{
    /**
     * @var ChannelManager $manager
     */
    protected $manager;

    /**
     * @param ChannelManager $manager
     */
    public function __construct(ChannelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $channels = $this->manager->getChannelChoices();
        if (0 === count($channels)) {
            throw new ConstraintDefinitionException('No channel is set in the application');
        }
        $constraint->choices = array_keys($channels);

        parent::validate($value, $constraint);
    }
}
