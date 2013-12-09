<?php

namespace Pim\Bundle\GridBundle\Sorter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface;

/**
 * Override OroGrid sorter to deal with flexible entity case
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SorterFactory implements SorterFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FieldDescriptionInterface $field
     * @param string                    $direction
     *
     * @throws \RunTimeException
     *
     * @return SorterInterface
     */
    public function create(FieldDescriptionInterface $field, $direction = null)
    {
        if (!$field->getName()) {
            throw new \RunTimeException('The field name must be defined for sorter');
        }

        if ($this->isFlexible($field)) {
            $sorter = $this->container->get('pim_grid.sorter.flexible');
        } else {
            $sorter = $this->container->get('oro_grid.sorter');
        }

        $sorter->initialize($field, $direction);

        return $sorter;
    }

    /**
     * Checks is field flexible or no
     *
     * @param FieldDescriptionInterface $field
     *
     * @return bool
     */
    protected function isFlexible(FieldDescriptionInterface $field)
    {
        return $field->getOption('flexible_name') ? true : false;
    }
}
