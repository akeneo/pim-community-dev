<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCatalogsByAttributeOptionQueryInterface
{
    /**
     * @return string[]
     */
    public function execute(AttributeOptionInterface $attributeOption): array;
}
