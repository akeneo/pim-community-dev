<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Form\FormView;

class DatagridView
{
    /**
     * @var DatagridInterface
     */
    protected $datagrid;

    /**
     * @var FormView;
     */
    protected $formView;

    public function __construct(DatagridInterface $datagrid)
    {
        $this->datagrid = $datagrid;
    }

    /**
     * @return DatagridInterface
     */
    public function getDatagrid()
    {
        return $this->datagrid;
    }

    /**
     * @return FormView
     */
    public function getFormView()
    {
        if (!$this->formView) {
            $this->formView = $this->datagrid->getForm()->createView();
        }

        return $this->formView;
    }
}
