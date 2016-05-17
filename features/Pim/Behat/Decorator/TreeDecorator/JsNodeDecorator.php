<?php

namespace Pim\Behat\Decorator\TreeDecorator;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Js node lib Decorator to ease the dom manipulation and assertion around it.
 */
class JsNodeDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    public function open()
    {
        $arrow = $this->spin(function () {
            return $this->find('css', 'ins');
        });

        $arrow->click();

        $this->spin(function () {
            return null === $this->find('css', 'a.jstree-loading');
        }, sprintf('Cannot open the node "%s"', $this->getName()));
    }

    public function select()
    {
        $checkbox = $this->find('css', '.jstree-checkbox');
        if ($checkbox) {
            $this->spin(function () use ($checkbox) {
                $checkbox->click();

                return true;
            }, 'Cannot check the node %s', $this->getName());
        } else {
            $this->find('css', 'a')->click();
        }
    }

    public function isOpen()
    {
        return !$this->hasClass('jstree-closed');
    }

    protected function getName() {
        return $this->find('css', 'a')->getText();
    }
}
