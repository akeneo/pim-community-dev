<?php

namespace Pim\Component\Catalog\Model;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityWithFamilyInterface extends EntityWithValuesInterface
{
    /**
     * @return null|FamilyInterface
     */
    public function getFamily(): ?FamilyInterface;
}
