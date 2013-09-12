<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

use Pim\Bundle\CatalogBundle\Form\Type\MassEditAction\ChangeStatusType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Batch operation to classify products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Classify extends AbstractMassEditAction
{
    /**
     * @var FlexibleManager $manager
     */
    protected $manager;

    /**
     * @param FlexibleManager $manager
     */
    public function __construct(FlexibleManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return new ChangeStatusType();
    }

    /**
     * {@inheritdoc}
     */
    public function perform(array $products)
    {

    }
}
