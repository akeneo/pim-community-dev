<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * A product value diff presenter
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
abstract class AbstractProductValuePresenter implements PresenterInterface, RendererAwareInterface
{
    use RendererAware;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data)
    {
        if ($data instanceof ValueInterface) {
            $attribute = $this->attributeRepository->findOneByIdentifier($data->getAttributeCode());

            return null !== $attribute && $this->supportsChange($attribute->getType());
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $change)
    {
        $change = array_merge($change, ['attribute' => $value->getAttributeCode()]);

        return $this->renderer->renderDiff(
            $this->normalizeData($value->getData()),
            $this->normalizeChange($change)
        );
    }

    /**
     * Whether or not this class can present the provided change
     *
     * @param string $attributeType
     *
     * @return bool
     */
    abstract protected function supportsChange($attributeType);

    /**
     * Normalize data
     *
     * @return array|string
     */
    protected function normalizeData($data)
    {
        return [];
    }

    /**
     * Normalize change
     *
     * @param array $change
     *
     * @return array|string
     */
    protected function normalizeChange(array $change)
    {
        return [];
    }
}
