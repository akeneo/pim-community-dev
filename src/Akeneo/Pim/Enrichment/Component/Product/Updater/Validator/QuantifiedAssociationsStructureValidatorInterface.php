<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Validator;

interface QuantifiedAssociationsStructureValidatorInterface
{
    public function validate(string $field, $data): void;
}
