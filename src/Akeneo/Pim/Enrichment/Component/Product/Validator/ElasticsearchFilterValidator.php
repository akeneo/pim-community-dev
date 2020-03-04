<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnavailableSpecificLocaleException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ElasticsearchFilterValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var AttributeValidatorHelper */
    private $attributeValidator;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeValidatorHelper $attributeValidator
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeValidator = $attributeValidator;
    }

    public function isLocaleValidForAttribute(string $attributeCode, ?string $localeCode): bool
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        try {
            $this->attributeValidator->validateLocale($attribute, $localeCode);
        } catch (UnavailableSpecificLocaleException $exception) {
            // We don't throw anything if the provided locale is not available
            // See https://akeneo.atlassian.net/browse/PIM-9113
        }

        return true;
    }

    public function isChannelValidForAttribute(string $attributeCode, ?string $channelCode): bool
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        $this->attributeValidator->validateScope($attribute, $channelCode);

        return true;
    }
}
