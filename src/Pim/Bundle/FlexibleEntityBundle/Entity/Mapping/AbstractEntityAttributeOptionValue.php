<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOptionValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute option value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractEntityAttributeOptionValue extends AbstractAttributeOptionValue
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var AbstractEntityAttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="AbstractEntityAttributeOption", inversedBy="optionValues")
     */
    protected $option;

    /**
     * Locale scope
     * @var string $locale
     *
     * @ORM\Column(name="locale_code", type="string", length=20, nullable=true)
     */
    protected $locale;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;
}
