<?php

namespace Pim\Component\Localization\Presenter;

/**
 * The PresenterRegistry register the presenters to display attribute values readable information. The matching
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

    /**
     * {@inheritdoc}
     */
    public function addPresenter(PresenterInterface $presenter)
    {
        $this->presenters[] = $presenter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPresenter($attributeType)
    {
        if (!empty($this->presenters)) {
            foreach ($this->presenters as $presenter) {
                if ($presenter->supports($attributeType)) {
                    return $presenter;
                }
            }
        }

        return null;
    }
}
