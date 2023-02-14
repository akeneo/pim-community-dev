<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Message;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluation implements TenantAwareInterface
{
    private string $tenantId;

    /**
     * @param string[] $criteriaToEvaluate
     */
    public function __construct(
        public readonly \DateTimeImmutable $messageCreatedAt,
        public readonly ProductUuidCollection $productUuids,
        public readonly ProductModelIdCollection $productModelIds,
        public readonly array $criteriaToEvaluate
    ) {
        Assert::allString($this->criteriaToEvaluate);
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public static function fromNormalized(array $data): self
    {
        Assert::keyExists($data, 'message_created_at');
        Assert::keyExists($data, 'product_uuids');
        Assert::keyExists($data, 'product_model_ids');
        Assert::keyExists($data, 'criteria');

        return new LaunchProductAndProductModelEvaluation(
            new \DateTimeImmutable($data['message_created_at']),
            ProductUuidCollection::fromStrings($data['product_uuids']),
            ProductModelIdCollection::fromStrings($data['product_model_ids']),
            $data['criteria']
        );
    }

    public function normalize(): array
    {
        return [
            'message_created_at' => $this->messageCreatedAt->format('c'),
            'product_uuids' => $this->productUuids->toArrayString(),
            'product_model_ids' => $this->productModelIds->toArrayString(),
            'criteria' => $this->criteriaToEvaluate,
        ];
    }
}
