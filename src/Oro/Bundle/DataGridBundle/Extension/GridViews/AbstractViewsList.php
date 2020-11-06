<?php

namespace Oro\Bundle\DataGridBundle\Extension\GridViews;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractViewsList
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var null|ArrayCollection */
    protected $views = null;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns an array of available views
     *
     * @return View[]
     */
    abstract protected function getViewsList(): array;

    /**
     * Public interface to retrieve list
     */
    public function getList(): ArrayCollection
    {
        if (!$this->views instanceof ArrayCollection) {
            $list = $this->getViewsList();
            $this->validate($list);

            $this->views = new ArrayCollection($list);
        }

        return $this->views;
    }

    /**
     * Find and returns view object by name
     *
     * @param string $name
     *
     * @return View|bool
     */
    public function getViewByName(string $name)
    {
        if (empty($name)) {
            return false;
        }

        $filtered = $this->getList()->filter(
            fn(View $view) => $view->getName() === $name
        );

        return $filtered->first();
    }

    /**
     * Returns array of choices for choice widget
     */
    public function toChoiceList(): array
    {
        $choices = [];

        /** @var View $view */
        foreach ($this->getList() as $view) {
            $choices[] = ['value' => $view->getName(), 'label' => $this->translator->trans($view->getName())];
        }

        return $choices;
    }

    /**
     * Returns metadata array
     */
    public function getMetadata(): array
    {
        $result = $this->getList()->map(
            fn(View $view) => $view->getMetadata()
        );

        return [
            'choices' => $this->toChoiceList(),
            'views'   => $result->toArray()
        ];
    }

    /**
     * Validates input array
     *
     * @param array $list
     *
     * @throws \InvalidArgumentException
     */
    protected function validate(array $list)
    {
        foreach ($list as $view) {
            if (!$view instanceof View) {
                throw new \InvalidArgumentException('List should contains only instances of View class');
            }
        }
    }
}
