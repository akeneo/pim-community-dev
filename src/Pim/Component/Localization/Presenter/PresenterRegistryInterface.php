<?php

namespace Pim\Component\Localization\Presenter;

/**
 * PresenterRegistryInterface
 *
 * Used to implement registries to manage presenters
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PresenterRegistryInterface
{
    /**
     * Register a Presenter to the registry
     *
     * @param PresenterInterface $presenter
     * @param string             $type
     */
    public function register(PresenterInterface $presenter, $type);

    /**
     * Get the first presenter supporting an attribute code
     *
     * @param string $code
     *
     * @return PresenterInterface|null
     */
    public function getProductValuePresenter($attributeType);

    /**
     * Get the first presenter supporting an attribute option
     *
     * @param string $optionName
     *
     * @return PresenterInterface|null
     */
    public function getAttributeOptionPresenter($optionName);
}
