<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Query;

use Akeneo\Platform\TailoredImport\Domain\Query\FindSystemTargetsInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InMemoryFindSystemTargets implements FindSystemTargetsInterface
{
    private array $fields;
    private TranslatorInterface $translator;

    public function __construct(array $fields, TranslatorInterface $translator)
    {
        $this->fields = $fields;
        $this->translator = $translator;
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
