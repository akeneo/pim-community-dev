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
        return $this->productRepository->findOneByIdentifier($this->getIdentifier($item));
    }

    /**
     * @param array $item
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function getIdentifier(array $item)
    {
        $identifierProperties = $this->productRepository->getIdentifierProperties();

        if (!isset($item[$identifierProperties[0]])) {
            throw new \RuntimeException(sprintf('Identifier property "%s" is expected', $identifierProperties[0]));
        }

        return $item[$identifierProperties[0]];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
