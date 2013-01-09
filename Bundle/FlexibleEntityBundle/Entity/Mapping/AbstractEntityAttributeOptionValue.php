<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOptionValue;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute option value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
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
     * @var string $localeCode
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=false)
     */
    protected $localeCode;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;

}
