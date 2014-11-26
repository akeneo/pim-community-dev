<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Registry of copiers
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CopierRegistry implements CopierRegistryInterface
{
    /** @var CopierInterface[] */
    protected $copiers = [];

    /**
     * {@inheritdoc}
     */
    public function register(CopierInterface $copier)
    {
        $this->copiers[] = $copier;
    }

    /**
     * {@inheritdoc}
     */
    public function get(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        foreach ($this->copiers as $copier) {
            if ($copier->supports($fromAttribute, $toAttribute)) {
                return $copier;
            }
        }

        throw new \LogicException(
            sprintf(
                'Source and destination attributes "%s" and "%s" are not supported by any copier',
                $fromAttribute->getCode(),
                $toAttribute->getCode()
            )
        );
    }
}
