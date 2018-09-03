<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;

/**
 * Association type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssociationTypeInterface extends
    TranslatableInterface,
    ReferableInterface,
    VersionableInterface,
    TimestampableInterface
{
    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     *
     * @return AssociationTypeInterface
     */
    public function setId($id);

    /**
     * Get code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     *
     * @return AssociationTypeInterface
     */
    public function setCode($code);

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set label
     *
     * @param string $label
     *
     * @return AssociationTypeInterface
     */
    public function setLabel($label);

    /**
     * Returns the label of the association type
     *
     * @return string
     */
    public function __toString();
}
