<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmeEnterprise\Bundle\AppBundle\Entity;

use Pim\Component\ReferenceData\Model\AbstractReferenceData;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Acme Fabric entity (used as multi reference data)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class Fabric extends AbstractReferenceData implements ReferenceDataInterface
{
    /** @var string */
    protected $name;

    /** @var int */
    protected $alternativeName;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set year
     *
     * @param int $year
     */
    public function setAlternativeName($year)
    {
        $this->alternativeName = $year;
    }

    /**
     * Get year
     *
     * @return int
     */
    public function getAlternativeName()
    {
        return $this->alternativeName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty()
    {
        return 'name';
    }
}
