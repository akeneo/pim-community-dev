<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface DocumentationBuilderInterface
{
    public function support($object): bool;

    public function buildDocumentation($object): DocumentationCollection;
}
