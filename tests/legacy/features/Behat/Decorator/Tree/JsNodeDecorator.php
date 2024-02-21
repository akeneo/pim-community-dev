<?php

namespace Pim\Behat\Decorator\Tree;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Js node lib Decorator to ease the dom manipulation and assertion around it.
 */
class JsNodeDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Open the node
     */
    public function open()
    {
        $arrow = $this->spin(function () {
            return $this->find('css', 'ins');
        }, 'Category tree arrow not found. Can not open category tree.');

        $arrow->click();

        $this->spin(function () {
            return null === $this->find('css', 'a.jstree-loading');
        }, sprintf('Cannot open the node "%s"', $this->getName()));
    }

    /**
     * Select the node
     */
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

    /**
     * Is the node open ?
     *
     * @return boolean
     */
    public function isOpen()
    {
        return !$this->hasClass('jstree-closed');
    }

    /**
     * Is the node selected ?
     *
     * @return boolean
     */
    public function isSelected()
    {
        if (null !== $this->find('css', '.jstree-checkbox .jstree-checked')) {
            return true;
        }

        if (null !== $this->find('css', '.jstree-selected')) {
            return true;
        }

        return false;
    }

    /**
     * Get the name of the node
     *
     * @return string
     */
    protected function getName()
    {
        return $this->find('css', 'a')->getText();
    }
}
