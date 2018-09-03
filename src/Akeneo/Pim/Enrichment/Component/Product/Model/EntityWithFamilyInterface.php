<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * Entity with a family.
 *
 * Having a family means having a structure based on family attributes.
 * For example, product attributes are all coming from its family.
 * Product model attributes come from an attribute set, which is a sub-part of family attributes.
 * The same goes for variant products.
 *
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
