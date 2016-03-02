<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Family requirements constraint
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRequirements extends Constraint
{
    /**
     * Violation message for not defined identifier requirement
     *
     * @var string
     */
    public $message = 'Family "%family%" should have requirements for identifier "%id%" and channel(s) "%channels%"';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_family_requirements_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
