<?php

namespace Pim\Bundle\ApiBundle\Checker;

use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;


/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface QueryParametersCheckerInterface
{
    /**
     * Checks $localeCodes if they exist.
     * Throws an exception if one of them does not exist or, if there is a $channel, one of them does not belong to it.
     *
     * @param array                 $locales
     * @param ChannelInterface|null $channel
     *
     * @throws UnprocessableEntityHttpException
     */
    public function checkLocalesParameters(array $locales, ChannelInterface $channel = null);

    /**
     * Checks $attributes if they exist. Thrown an exception if one of them does not exist.
     *
     * @param array $attributes
     *
     * @throws UnprocessableEntityHttpException
     */
    public function checkAttributesParameters(array $attributes);

    /**
     * Checks $attributes if they exist. Thrown an exception if one of them does not exist.
     *
     * @param string $attributes
     *
     * @throws UnprocessableEntityHttpException
     */
    public function checkCategoriesParameters($categories);
}
