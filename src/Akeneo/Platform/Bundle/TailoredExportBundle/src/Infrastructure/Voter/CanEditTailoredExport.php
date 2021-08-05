<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Voter;

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

/**
 * @author Julien Sanchez <julien@akeneo.com>
 */
class CanEditTailoredExport
{
    protected GetAllViewableLocalesForUserInterface $getAllViewableLocales;
    protected GetViewableAttributeCodesForUserInterface $getViewableAttributes;
    protected GetAttributes $getAttributes;

    public function __construct(
        GetAllViewableLocalesForUserInterface $getAllViewableLocales,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        GetAttributes $getAttributes
    ) {
        $this->getAllViewableLocales = $getAllViewableLocales;
        $this->getViewableAttributes = $getViewableAttributes;
        $this->getAttributes = $getAttributes;
    }

    public function execute(JobInstance $jobInstance, int $userId): bool
    {
        $columns = $jobInstance->getRawParameters()['columns'] ?? null;

        if (null === $columns) {
            return false;
        }

        return $this->canEditAllAttributes($columns, $userId) && $this->canEditAllLocales($columns, $userId);
    }

    private function canEditAllAttributes(array $columns, int $userId): bool
    {
        $jobAttributeCodes = array_unique(array_reduce($columns, function (array $accumulator, array $column) {
            $attributeSources = array_filter(
                $column['sources'],
                static fn (array $source) => AttributeSource::TYPE === $source['type']
            );
            $attributeCodes = array_map(static fn (array $source) => $source['code'], $attributeSources);

            return array_merge($accumulator, $attributeCodes);
        }, []));
        $notDeletedJobAttributes = $this->getAttributes->forCodes($jobAttributeCodes);
        $notDeletedJobAttributeCodes = array_map(static fn (Attribute $attribute) => $attribute->code(), $notDeletedJobAttributes);

        $viewableAttributes = $this->getViewableAttributes->forAttributeCodes($notDeletedJobAttributeCodes, $userId);

        return array_intersect($viewableAttributes, $notDeletedJobAttributeCodes) === $notDeletedJobAttributeCodes;
    }

    private function canEditAllLocales(array $columns, int $userId): bool
    {
        $jobLocaleCodes = array_unique(array_reduce($columns, function (array $accumulator, array $column) {
            $localeCodes = array_map(static fn (array $source) => $source['locale'], $column['sources']);

            return array_merge($accumulator, array_filter($localeCodes));
        }, []));
        $viewableLocales = $this->getAllViewableLocales->fetchAll($userId);

        return array_intersect($jobLocaleCodes, $viewableLocales) === $jobLocaleCodes;
    }
}
