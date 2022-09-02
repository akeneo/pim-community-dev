<?php

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query;

use Akeneo\Platform\TailoredExport\Domain\Query\FindSystemSourcesInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InMemoryFindSystemSources implements FindSystemSourcesInterface
{
    public function __construct(
        private array $fields,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $localeCode, int $limit, int $offset = 0, string $search = null): array
    {
        $filteredFields = $this->filterSystemFieldByText($localeCode, $this->fields, $search);

        return array_slice($filteredFields, $offset, $limit);
    }

    private function filterSystemFieldByText(string $localeCode, array $fields, ?string $search): array
    {
        if (null === $search || '' === trim($search)) {
            return $fields;
        }

        $search = strtolower($search);

        return array_filter($fields, function (string $field) use ($search, $localeCode): bool {
            $label = $this->translator->trans(sprintf('pim_common.%s', $field), [], null, $localeCode);

            return str_contains(strtolower($label), $search);
        });
    }
}
