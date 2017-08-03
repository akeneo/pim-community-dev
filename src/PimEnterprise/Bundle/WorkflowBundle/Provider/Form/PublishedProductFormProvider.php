<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Provider\Form;

use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;

/**
 * Form provider for published product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class PublishedProductFormProvider implements FormProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getForm($product): string
    {
        return 'pimee-published-product-view-form';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof PublishedProductInterface;
    }
}
