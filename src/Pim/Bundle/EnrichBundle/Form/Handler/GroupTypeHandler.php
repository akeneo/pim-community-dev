<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler for group type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeHandler implements HandlerInterface
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /**
     * Constructor for handler
     *
     * @param FormInterface $form    Form called
     * @param Request       $request Web request
     * @param ObjectManager $manager Storage manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($groupType)
    {
        $this->form->setData($groupType);

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($groupType);

                return true;
            }
        }

        return false;
    }

    /**
     * Call when form is valid
     *
     * @param GroupType $groupType
     */
    protected function onSuccess(GroupType $groupType)
    {
        $this->manager->persist($groupType);
        $this->manager->flush();
    }
}
