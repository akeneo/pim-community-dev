<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException as ProductInvalidArgumentException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LegacyProductUpdater implements ProductUpdater
{
    public function __construct(
        private ValidatorInterface $productValidator,
        private ProductBuilderInterface $productBuilder,
        private ObjectUpdaterInterface $updater,
        private SaverInterface $saver,
        private ProductBuilderInterface $variantProductBuilder,
        private AttributeFilterInterface $productAttributeFilter,
        private EventDispatcherInterface $eventDispatcher,
        private RemoveParentInterface $removeParent,
        private Connection $connection,
    ) {
    }

    public function update(array $data): void
    {
        if (isset($data['parent'])) {
            $product = $this->variantProductBuilder->createProduct($data['identifier']);
        } else {
            $product = $this->productBuilder->createProduct();
        }

        $this->updateProduct($product, $data, 'post_products');
        $this->validateProduct($product);
        $this->saver->save($product);
    }

    private function updateProduct(ProductInterface $product, array $data, string $anchor): void
    {
        if (array_key_exists('variant_group', $data)) {
            throw new DocumentedHttpException(
                Documentation::URL_DOCUMENTATION . 'products-with-variants.html',
                'Property "variant_group" does not exist anymore. Check the link below to understand why.'
            );
        }

        try {
            if ($this->needUpdateFromVariantToSimple($product, $data)) {
                $this->removeParent->from($product);
            }

            if (isset($data['parent']) || $product->isVariant()) {
                $data = $this->productAttributeFilter->filter($data);
            }

            $this->updater->update($product, $data);
        } catch (\Exception $exception) {
            if ($exception instanceof DomainErrorInterface) {
                $this->eventDispatcher->dispatch(new ProductDomainErrorEvent($exception, $product));
            } else {
                $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            }

            if ($exception instanceof PropertyException) {
                $message = $exception->getMessage();
                $matches = [];
                if (preg_match('/^Property "associations" expects a valid product identifier. The product does not exist, "(?P<identifier>.*)" given.$/', $message, $matches)) {
                    $message = sprintf(
                        'Property "associations" expects a valid product uuid. The product does not exist, "%s" given.',
                        $this->getUuidFromIdentifier($matches['identifier'])
                    );
                }

                throw new DocumentedHttpException(
                    Documentation::URL . $anchor,
                    sprintf('%s Check the expected format on the API documentation.', $message),
                    $exception
                );
            }

            if ($exception instanceof TwoWayAssociationWithTheSameProductException) {
                throw new DocumentedHttpException(
                    TwoWayAssociationWithTheSameProductException::TWO_WAY_ASSOCIATIONS_HELP_URL,
                    TwoWayAssociationWithTheSameProductException::TWO_WAY_ASSOCIATIONS_ERROR_MESSAGE,
                    $exception
                );
            }

            if ($exception instanceof InvalidArgumentException || $exception instanceof ProductInvalidArgumentException) {
                throw new AccessDeniedHttpException($exception->getMessage(), $exception);
            }

            throw $exception;
        }
    }

    private function validateProduct(ProductInterface $product): void
    {
        $violations = $this->productValidator->validate($product, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            $this->eventDispatcher->dispatch(new ProductValidationErrorEvent($violations, $product));

            throw new ViolationHttpException($violations);
        }
    }

    private function needUpdateFromVariantToSimple(ProductInterface $product, array $data): bool
    {
        return null !== $product->getCreated() && $product->isVariant() &&
            array_key_exists('parent', $data) && null === $data['parent'];
    }

    private function getUuidFromIdentifier(string $productIdentifier): ?UuidInterface
    {
        $uuid = $this->connection->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?',
            [$productIdentifier]
        );

        return false === $uuid ? null : Uuid::fromString($uuid);
    }
}
