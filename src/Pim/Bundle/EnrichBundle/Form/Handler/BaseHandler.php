<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base handler
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseHandler implements HandlerInterface
{
    /** @var FormInterface */
    protected $form;

    /**  @var Request */
    protected $request;

    /** @var SaverInterface */
    protected $saver;

    /**
     * Constructor for handler
     *
     * @param FormInterface  $form    Form called
     * @param Request        $request Web request
     * @param SaverInterface $saver   Entity saver
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        SaverInterface $saver
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->saver   = $saver;
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $this->form->setData($entity);
        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);
            if ($this->form->isValid()) {
                $this->saver->save($entity);

                return true;
            }
        }

        return false;
    }
}
