<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Present changes on numbers
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class NumberPresenter extends AbstractProductValuePresenter
{
    /** @var BasePresenterInterface */
    protected $numberPresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        BasePresenterInterface $numberPresenter,
        LocaleResolver $localeResolver
    ) {
        parent::__construct($attributeRepository);

        $this->numberPresenter = $numberPresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::NUMBER === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->numberPresenter->present($data, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->numberPresenter->present($change['data'], $options);
    }
}
