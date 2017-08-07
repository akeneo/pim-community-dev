<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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

    /**  @var RequestStack */
    protected $requestStack;

    /** @var SaverInterface */
    protected $saver;

    /**
     * Constructor for handler
     *
     * @param FormInterface  $form         Form called
     * @param RequestStack   $requestStack Web request
     * @param SaverInterface $saver        Entity saver
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        SaverInterface $saver
    ) {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->saver = $saver;
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $this->form->setData($entity);
        if ($this->requestStack->getCurrentRequest()->isMethod('POST')) {
            $this->form->handleRequest($this->requestStack->getCurrentRequest());
            if ($this->form->isValid()) {
                $this->saver->save($entity);

                return true;
            }
        }

        return false;
    }
}
