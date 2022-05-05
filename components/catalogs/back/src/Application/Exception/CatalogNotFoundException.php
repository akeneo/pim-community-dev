<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Exception;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogNotFoundException extends \Exception
{
    public function __construct(
        private string $id
    ) {
        parent::__construct();
    }

    public function getId(): string
    {
        return $this->id;
    }
}
