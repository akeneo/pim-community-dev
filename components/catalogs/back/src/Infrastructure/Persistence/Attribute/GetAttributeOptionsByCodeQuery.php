<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAttributeOptionsByCodeQuery implements GetAttributeOptionsByCodeQueryInterface
{
    public function __construct(
        private SearchableRepositoryInterface $searchableAttributeOptionsRepository,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $attribute, array $codes, string $locale = 'en_US'): array
    {
        $options = $this->searchableAttributeOptionsRepository->findBySearch(
            null,
            [
                'identifier' => $attribute,
                'identifiers' => $codes,
            ],
        );
        $normalize = function (AttributeOptionInterface $option) use ($locale): array {
            /** @var string $code */
            $code = $option->getCode();

            return [
                'code' => $code,
                'label' => $option->setLocale($locale)->getOptionValue()?->getLabel() ?: '[' . $code . ']',
            ];
        };

        return \array_map($normalize, $options);
    }
}
