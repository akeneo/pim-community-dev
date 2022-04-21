<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Import;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * Converts supplier data coming from a flat file to a structured format.
 */
class FlatToStandard implements ArrayConverterInterface
{
    public function __construct(private FieldsRequirementChecker $fieldChecker)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $supplier, array $options = []): array
    {
        $this->fieldChecker->checkFieldsPresence($supplier, ['supplier_code', 'supplier_label', 'contributor_emails']);
        $this->fieldChecker->checkFieldsFilling($supplier, ['supplier_code', 'supplier_label']);

        return [
            'supplier_code' => (string) $supplier['supplier_code'],
            'supplier_label' => isset($supplier['supplier_label']) && $supplier['supplier_label']
                ? $supplier['supplier_label']
                : null
            ,
            'contributor_emails' => $this->convertContributorEmails($supplier),
        ];
    }

    private function convertContributorEmails(array $supplier): ?array
    {
        if (!isset($supplier['contributor_emails'])) {
            return null;
        }

        if (empty($supplier['contributor_emails'])) {
            return [];
        }

        return array_map('trim', explode(',', $supplier['contributor_emails']));
    }
}
