<?php

namespace PimEnterprise\Behat\Decorator\WidgetDecorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Proposal Widget Decorator to ease the dom manipulation and assertion around it.
 */
class ProposalWidgetDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $user
     * @param string $product
     * @return mixed
     *
     * @throws \Context\Spin\TimeoutException
     * @throws \Exception
     */
    public function followProposalLink($user, $product)
    {
        $selector = sprintf('td:contains("%s")', $user);

        $childNode = $this->spin(function () use ($selector) {
            return $this->element->findAll('css', $selector);
        }, sprintf('Unable to find node "%s" in the proposal widget', $selector));

        $selector = sprintf('tr:contains("%s") a.product-review', $product);
        $nodeElement = null;
        foreach ($childNode as $item) {
            $element = $item->getParent()->find('css', $selector);

            if (null !== $element) {
                $nodeElement = $element;
            }
        }

        if (null === $nodeElement) {
            throw new \Exception('Element not found');
        }

        $nodeElement->click();
    }
}
