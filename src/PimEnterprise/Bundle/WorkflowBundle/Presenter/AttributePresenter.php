<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Present an attribute
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributePresenter implements PresenterInterface, TwigAwareInterface
{
    use TwigAware;

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
        $parts = [];

        if ($data->isLocalizable()) {
            $parts[] = $this
                ->twig
                ->getExtension('pim_locale_extension')
                ->flag(
                    $this->twig,
                    $change['__context__']['locale'],
                    false
                );
        }

        if ($data->isScopable() && isset($change['__context__']['scope'])) {
            $parts[] = $change['__context__']['scope'];
        }

        $parts[] = (string) $data;

        return join(' - ', $parts);
    }
}
