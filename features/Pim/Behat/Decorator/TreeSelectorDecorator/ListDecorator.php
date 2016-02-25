<?php

namespace Pim\Behat\Decorator\TreeSelectorDecorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * List tree selector decorator to ease the dom manipulation and assertion arround it.
 */
class ListDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $treeName
     */
    public function selectTree($treeName)
    {
        $link = $this->spin(function () use ($treeName) {
            return $this->find('css', sprintf('li a:contains("%s")', $treeName));
        }, sprintf('Tree "%s" not found in selector', $treeName));

        $link->click();
    }
}
