<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Interface to implement for any entity that should be aware of any associations it is holding.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityWithQuantifiedAssociationsInterface
{
    public function setQuantifiedAssociations(array $quantifiedAssociations);

    public function getQuantifiedAssociations();

    public function getAllLinkedProductIds();

    public function getAllLinkedProductModelIds();

    public function getQuantifiedAssociationsWithIdentifiersAndCodes(array $productIdentifiers, array $productModelCodes): array;

    public function setQuantifiedAssociationsWithIds(array $newQuantifiedAssociations, array $productIds, array $productModelIds);
}
