<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DocumentationBuilderRegistry
{
    /** @var DocumentationBuilderInterface[] */
    private $builders;

    /**
     * @param DocumentationBuilderInterface[] $documentationBuilderList
     */
    public function __construct(iterable $documentationBuilderList)
    {
        $this->builders = $documentationBuilderList;
    }

    public function getDocumentation(object $object): ?DocumentationCollection
    {
        foreach ($this->builders as $builder) {
            if ($builder->support($object)) {
                return $builder->buildDocumentation($object);
            }
        }

        return null;
    }
}
