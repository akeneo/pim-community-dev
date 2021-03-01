<?php

namespace Pim\Behat\Decorator\Tree;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class TreeDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    public function findNodeInTree(string $nodeName): TreeDecorator
    {
        return $this->spin(function () use ($nodeName) {
            $trees = $this->findAll('css', 'li[role=treeitem]');
            foreach ($trees as $tree) {
                if (strpos($tree->getText(), $nodeName) === 0) {
                    return $this->decorate($tree, [TreeDecorator::class]);
                }
            }

            return false;
        }, sprintf('Node "%s" not found, found %s',
                $nodeName,
                join(', ', array_map(function ($tree) {
                    return sprintf('"%s"', $tree->getText());
                }, $this->findAll('css', 'li[role=treeitem]'))))
        );
    }

    public function select()
    {
        $checkbox = $this->find('css', 'div[role=checkbox]');
        if (null === $checkbox) {
            throw new ElementNotFoundException($this->getDriver(), 'div[role=checkbox]');
        }
        if ($checkbox->getAttribute('aria-checked') === 'false') {
            $checkbox->click();
        }
    }

    public function expandNode(string $nodeName): void
    {
        $this->spin(function () use ($nodeName) {
            $tree = $this->findNodeInTree($nodeName);
            if ($tree->getAttribute('aria-expanded') === 'false') {
                $tree->find('css', 'button')->click();

                return false;
            }

            return $this->findNodeInTree($nodeName)->getAttribute('aria-expanded') === 'true';
        }, sprintf('Unable to open %s', $nodeName));
    }
}
