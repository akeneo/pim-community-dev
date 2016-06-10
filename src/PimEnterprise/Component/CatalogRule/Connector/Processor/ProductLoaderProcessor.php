<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Connector\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Load a product from its identifier.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProductLoaderProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if (is_object($item)) {
            return $item;
        }

        return $this->productRepository->findOneByIdentifier($this->getIdentifier($item));
    }

    /**
     * Get the identifier value from a item formatted in pivot format.
     * This function uses the fact that pivot format have only one value for identifier attribute.
     *
     * With this $item example:
     * [
     *   'sku' => [
     *     [
     *       'scope'  => null,
     *       'locale' => null,
     *       'data'   => 'boot-123'
     *     ]
     *   ],
     *   'color' => [ ... ]
     * ]
     * This function will return 'boot-123'.
     *
     * @param array $item
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function getIdentifier(array $item)
    {
        $identifierProperties = $this->productRepository->getIdentifierProperties();
        $identifierProperty = $identifierProperties[0];

        if (!isset($item[$identifierProperty][0]['data'])) {
            throw new \RuntimeException(sprintf('Identifier property "%s" is expected', $identifierProperty));
        }

        return $item[$identifierProperty][0]['data'];
    }
}
