<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;

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
    public function getPresenterByAttributeCode($code);

    /**
     * Get the first presenter supporting an attribute type
     *
     * @param string $attributeType
     *
     * @return PresenterInterface|null
     */
    public function getPresenterByAttributeType($attributeType);

    /**
     * Get the first presenter supporting a field code
     *
     * @param string $code
     *
     * @return PresenterInterface|null
     */
    public function getPresenterByFieldCode($code);

    /**
     * Get the first presenter supporting an attribute option
     *
     * @param string $optionName
     *
     * @return PresenterInterface|null
     */
    public function getAttributeOptionPresenter($optionName);
}
