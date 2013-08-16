<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Pim\Bundle\ProductBundle\Manager\ChannelManager;
use Symfony\Component\Validator\Constraint;

/**
 * Validate that the selected channel exists
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelValidator extends ChoiceValidator
{
    protected $manager;

    /**
     * @param ChannelManager $manager
     */
    public function __construct(ChannelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $constraint->choices = array_keys($this->manager->getChannelChoices());

        parent::validate($value, $constraint);
    }
}
