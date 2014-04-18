<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use JMS\Serializer\Annotation\Exclude;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * A batch operation operator
 * Applies batch operations to families passed in the form of QueryBuilder
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Exclude
 */
class FamilyMassEditActionOperator extends AbstractMassEditActionOperator
{
    public function finalizeOperation()
    {
        throw new \Exception('Not implemented yet');
    }
}
