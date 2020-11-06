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
    public function register(PresenterInterface $presenter, string $type);

    /**
     * Get the first presenter supporting an attribute code
     *
     * @param string $code
     */
    public function getPresenterByAttributeCode(string $code): ?\Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;

    /**
     * Get the first presenter supporting an attribute type
     *
     * @param string $attributeType
     */
    public function getPresenterByAttributeType(string $attributeType): ?\Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;

    /**
     * Get the first presenter supporting a field code
     *
     * @param string $code
     */
    public function getPresenterByFieldCode(string $code): ?\Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;

    /**
     * Get the first presenter supporting an attribute option
     *
     * @param string $optionName
     */
    public function getAttributeOptionPresenter(string $optionName): ?\Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
}
