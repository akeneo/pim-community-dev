<?php

namespace Pim\Bundle\CatalogBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Form handler for channel
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * Constructor for handler
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
     * Process method for handler
     * @param Channel $channel
     *
     * @return boolean
     */
    public function process(Channel $channel)
    {
        $this->form->setData($channel);

        if ($this->request->isMethod('POST')) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($channel);

                return true;
            }
        }

        return false;
    }

    /**
     * Call when form is valid
     * @param Channel $channel
     */
    protected function onSuccess(Channel $channel)
    {
        $this->manager->persist($channel);
        foreach ($channel->getLocales() as $locale) {
            $this->manager->persist($locale);
        }
        $this->manager->flush();
    }
}
