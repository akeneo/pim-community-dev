<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryChoicesRepository extends NestedTreeRepository implements ChoicesProviderInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(array $options)
    {
        $children = $this->getChildren(null, true);

        $choices = [];
        foreach ($children as $child) {
            $choices[$child->getId()] = $child->getTranslation()->getLabel();
        }

        return $choices;
    }
}
