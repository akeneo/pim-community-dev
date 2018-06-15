<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\FamilyVariant\Query;

/**
 * Find family variants identifiers by their attribute axes.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyVariantsByAttributeAxesInterface
{
    /**
     * Find family variants identifiers by their attribute axes.
     *
     * @param array $attributeAxesCodes
     *
     * @return array
     */
    public function findIdentifiers(array $attributeAxesCodes): array;
}
