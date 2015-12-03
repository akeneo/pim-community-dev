<?php

namespace Pim\Component\Localization\Presenter;

/**
 * The PresenterRegistry registers the presenters to display attribute values readable information. The matching
 * presenters are returned from an attributeType
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PresenterRegistry implements PresenterRegistryInterface
{
    /** @var PresenterInterface[] */
    protected $presenters = [];

    /** @var PresenterInterface[] */
    protected $optionPresenters = [];

    /**
     * {@inheritdoc}
     */
    public function registerPresenter(PresenterInterface $presenter)
    {
        $this->presenters[] = $presenter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function registerAttributeOptionPresenter(PresenterInterface $presenter)
    {
        $this->optionPresenters[] = $presenter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPresenter($attributeType)
    {
        return $this->getSupportedPresenter($this->presenters, $attributeType);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionPresenter($attributeName)
    {
        return $this->getSupportedPresenter($this->optionPresenters, $attributeName);
    }

    /**
     * Returning the first presenter supporting the value
     *
     * @param PresenterInterface[] $presenters
     * @param string               $value
     *
     * @return PresenterInterface|null
     */
    protected function getSupportedPresenter(array $presenters, $value)
    {
        foreach ($presenters as $presenter) {
            if ($presenter->supports($value)) {
                return $presenter;
            }
        }

        return null;
    }
}
