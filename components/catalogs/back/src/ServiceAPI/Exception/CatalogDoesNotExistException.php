<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Exception;

/**
 * Exception thrown when you try to get products, product identifiers or product uuids from a non-existent catalog.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CatalogDoesNotExistException extends \Exception
{
}
