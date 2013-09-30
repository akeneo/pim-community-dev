<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Oro\Bundle\MeasureBundle\Manager\MeasureManager;
use Pim\Bundle\CatalogBundle\AttributeType\MetricType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_metric';
    protected $backendType = 'metric';
    protected $formType = 'text';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        $measureManager = new MeasureManager();

        return new MetricType($this->backendType, $this->formType, $this->guesser, $measureManager);
    }
}
