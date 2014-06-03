<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Present an attribute
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributePresenter implements PresenterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($data, array $change)
    {
        return $data instanceof AbstractAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function present($data, array $change)
    {
        $result = (string) $data;

        if ($data->isLocalizable()) {
            $result .= ' <i class="icon-globe"></i>';
        }

        if ($data->isScopable()) {
            $result = $change['scope'] . ' - ' . $result;
        }

        return $result;
    }
}
