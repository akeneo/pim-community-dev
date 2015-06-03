<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Present an attribute
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class AttributePresenter implements PresenterInterface, TwigAwareInterface
{
    use TwigAware;

    /**
     * {@inheritdoc}
     */
    public function supports($data)
    {
        return $data instanceof AttributeInterface;
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
                    $change['locale'],
                    false
                );
        }

        if ($data->isScopable() && isset($change['scope'])) {
            $parts[] = $change['scope'];
        }

        $parts[] = $data->getLabel();

        return implode(' - ', $parts);
    }
}
