<?php

namespace Akeneo\Pim\Structure\Component\Repository;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Repository interface for attribute requirements
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRequirementRepositoryInterface extends ObjectRepository
{
    /**
     * Returns attributes requirements codes and channel code for the given family
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    public function findRequiredAttributesCodesByFamily(FamilyInterface $family);
}
