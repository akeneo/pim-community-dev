<?php

namespace Akeneo\Channel\Component\Model;

use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;

/**
 * Currency interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CurrencyInterface extends ReferableInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return CurrencyInterface
     */
    public function setCode($code);

    /**
     * @return bool
     */
    public function isActivated();

    /**
     * @param bool $activated
     *
     * @return CurrencyInterface
     */
    public function setActivated($activated);

    /**
     * Toggle activation
     *
     * @return CurrencyInterface
     */
    public function toggleActivation();
}
