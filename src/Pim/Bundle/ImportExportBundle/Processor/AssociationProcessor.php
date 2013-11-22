<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\CatalogBundle\Entity\Association;

/**
 * Valid association creation (or update) processor
 *
 * Allow to bind input data to an association and validate it
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationProcessor extends AbstractEntityProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $association = $this->getAssociation($item);

        foreach ($item as $key => $value) {
            if (preg_match('/^label-(.+)/', $key, $matches)) {
                $association->setLocale($matches[1]);
                $association->setLabel($value);
            }
        }
        $association->setLocale(null);

        $this->validate($association, $item);

        return $association;
    }

    /**
     * Create an association or get it if already exists
     *
     * @param array $item
     *
     * @return Association
     */
    private function getAssociation(array $item)
    {
        $association = $this->findAssociation($item['code']);

        if (!$association) {
            $association = new Association();
            $association->setCode($item['code']);
        }

        return $association;
    }

    /**
     * Find association by code
     *
     * @param string $code
     *
     * @return Association|null
     */
    private function findAssociation($code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:Association')
            ->findOneBy(array('code' => $code));
    }
}
