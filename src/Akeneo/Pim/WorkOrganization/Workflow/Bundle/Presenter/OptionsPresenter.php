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

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Present changes on options data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class OptionsPresenter extends AbstractProductValuePresenter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $optionRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        $options = [];
        if (is_iterable($formerData)) {
            foreach ($formerData as $optionCode) {
                $options[] = $this->optionRepository->findOneByIdentifier(
                    $change['attribute'] . '.' . $optionCode
                );
            }
        }

        return [
            'before_data' => $this->normalizeData($options),
            'after_data' => $this->normalizeChange($change),
        ];

        return $this->renderer->renderDiff(
            $this->normalizeData($options),
            $this->normalizeChange($change)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::OPTION_MULTI_SELECT === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $result = [];
        foreach ($data as $option) {
            $result[] = (string) $option;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (null === $change['data']) {
            return null;
        }

        $result = [];

        foreach ($change['data'] as $option) {
            $identifier = sprintf('%s.%s', $change['attribute'], $option);
            $result[] = (string) $this->optionRepository->findOneByIdentifier($identifier);
        }

        return $result;
    }
}
