<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\WebhookEvent\WebhookEventDataBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEventDataBuilder implements WebhookEventDataBuilder
{
    private $productBuilder;
    private $productUpdater;
    private $productValidator;
    private $externalApiNormalizer;

    public function __construct(
        ProductBuilder $productBuilder,
        ProductUpdater $productUpdater,
        ValidatorInterface $productValidator,
        NormalizerInterface $externalApiNormalizer
    ) {
        $this->productBuilder = $productBuilder;
        $this->productUpdater = $productUpdater;
        $this->productValidator = $productValidator;
        $this->externalApiNormalizer = $externalApiNormalizer;
    }

    /**
     * @param ProductCreated|ProductUpdated $businessEvent
     */
    public function build(BusinessEventInterface $businessEvent, array $context): array
    {
        if (false === $this->supports($businessEvent)) {
            throw new \InvalidArgumentException();
        }

        /*
        $data = $businessEvent->data();

        $product = $this->productBuilder->createProduct($data['identifier'], $data['family'] ?? null);
        $this->productUpdater->update($product, $data);

        $this->productValidator->validate($product);

        return $this->externalApiNormalizer->normalize($product, 'external_api');
        */

        return $businessEvent->data();
    }

    public function supports(BusinessEventInterface $businessEvent): bool
    {
        return $businessEvent instanceof ProductUpdated || $businessEvent instanceof ProductCreated;
    }
}
