<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\Model\FamilyInterface;

/**
 * Product builder interface
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyBuilderInterface
{
    /**
     * Create family with its identifier value,
     *  - sets the identifier data if wanted
     *
     * @param bool $withIdentifier
     *
     * @return FamilyInterface
     */
    public function createFamily($withIdentifier = false);

    /**
     * @param FamilyInterface $family
     * @param array           $data
     *
     * @throws \InvalidArgumentException
     *
     * @return null
     */
    public function setAttributeRequirements(FamilyInterface $family, array $data);

    /**
     * @param FamilyInterface $family
     * @param array           $data
     *
     * @return null
     */
    public function setLabels(FamilyInterface $family, array $data);

    /**
     * @param FamilyInterface $family
     * @param array           $data
     *
     * @throws \InvalidArgumentException
     *
     * @return null
     */
    public function addAttributes(FamilyInterface $family, array $data);

    /**
     * @param FamilyInterface $family
     * @param string          $data
     *
     * @throws \InvalidArgumentException
     *
     * @return null
     */
    public function setAttributeAsLabel(FamilyInterface $family, $data);
}
