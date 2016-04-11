<?php

namespace Pim\Behat\Decorator\TreeSelectorDecorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Select tree selector decorator to ease the dom manipulation and assertion arround it.
 */
class SelectDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $treeName
     */
    public function selectTree($treeName)
    {
        $this->spin(function () use ($treeName) {
            $this->selectOption($treeName);

            return true;
        }, sprintf('Tree "%s" not found in selector', $treeName));
    }
}
