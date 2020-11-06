<?php

namespace Acme\Bundle\AppBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractReferenceData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;

/**
 * Acme Fabric entity (used as multi reference data)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Fabric extends AbstractReferenceData implements ReferenceDataInterface
{
    protected ?string $name = null;

    protected ?int $alternativeName = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Set year
     *
     * @param int $year
     */
    public function setAlternativeName(int $year): void
    {
        $this->alternativeName = $year;
    }

    /**
     * Get year
     */
    public function getAlternativeName(): ?int
    {
        return $this->alternativeName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty(): ?string
    {
        return 'name';
    }
}
