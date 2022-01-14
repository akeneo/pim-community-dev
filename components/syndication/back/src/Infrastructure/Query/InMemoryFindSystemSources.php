<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Query;

use Akeneo\Platform\Syndication\Domain\Query\FindSystemSourcesInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InMemoryFindSystemSources implements FindSystemSourcesInterface
{
    /** @var array<array<string>> */
    private array $fields;
    private TranslatorInterface $translator;

    public function __construct(array $fields, TranslatorInterface $translator)
    {
        $this->fields = $fields;
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $localeCode, int $limit, int $offset, ?string $search, string $type): array
    {
        $filteredFields = $this->filterSystemFieldByText($localeCode, $this->fields[$type] ?? [], $search);

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
