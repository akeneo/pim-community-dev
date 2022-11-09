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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Voter;

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class CanEditTailoredImport
{
    public function __construct(
        protected FindAllViewableLocalesForUser $findAllViewableLocalesForUser,
        protected GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        protected GetAttributes $getAttributes,
    ) {
    }

    public function execute(JobInstance $jobInstance, int $userId): bool
    {
        $dataMappings = $jobInstance->getRawParameters()['import_structure']['data_mappings'] ?? null;

        if (null === $dataMappings) {
            return false;
        }

        return $this->canEditAllAttributes($dataMappings, $userId) && $this->canEditAllLocales($dataMappings, $userId);
    }

    private function canEditAllAttributes(array $dataMappings, int $userId): bool
    {
        $attributeTargetCodes = [];
        foreach ($dataMappings as $dataMapping) {
            if (
                AttributeTarget::TYPE === $dataMapping['target']['type']
                && !\in_array($dataMapping['target']['code'], $attributeTargetCodes)
            ) {
                $attributeTargetCodes[] = $dataMapping['target']['code'];
            }
        }

        $notDeletedJobAttributeCodes = \array_keys(\array_filter($this->getAttributes->forCodes($attributeTargetCodes)));
        $viewableAttributes = $this->getViewableAttributes->forAttributeCodes($notDeletedJobAttributeCodes, $userId);

        return empty(\array_diff($notDeletedJobAttributeCodes, $viewableAttributes));
    }

    private function canEditAllLocales(array $dataMappings, int $userId): bool
    {
        $targetLocaleCodes = [];
        foreach ($dataMappings as $dataMapping) {
            if (
                isset($dataMapping['target']['locale'])
                && !\in_array($dataMapping['target']['locale'], $targetLocaleCodes)
            ) {
                $targetLocaleCodes[] = $dataMapping['target']['locale'];
            }
        }

        $viewableLocaleCodes = \array_map(
            static fn (Locale $locale) => $locale->getCode(),
            $this->findAllViewableLocalesForUser->findAll($userId),
        );

        return empty(\array_diff($targetLocaleCodes, $viewableLocaleCodes));
    }
}
