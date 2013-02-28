<?php

namespace Pim\Bundle\ConfigBundle\Controller;

use Pim\Bundle\ConfigBundle\Entity\Language;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Language controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/language")
 */
class LanguageController extends Controller
{

    /**
     * List languages
     *
     * @return multitype
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $languages = $this->getLanguageManager()->getEntityRepository()->findAll();

        return array('languages' => $languages);
    }

    /**
     * Get language manager
     * @return Oro\Bundle\FlexibleEntityBundle\Manager\SimpleManager
     */
    protected function getLanguageManager()
    {
        return $this->get('language_manager');
    }

    /**
     * Create language
     *
     * @Route("/create")
     * @Template("PimConfigBundle:Language:edit.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $language = $this->getLanguageManager()->createEntity();

        return $this->editAction($language);
    }

    /**
     * Edit language
     *
     * @param Language $language
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Language $language)
    {
        if ($this->get('pim_config.form.handler.language')->process($language)) {
            $this->get('session')->getFlashBag()->add('success', 'Language successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_config_language_index')
            );
        }

        return array(
            'form' => $this->get('pim_config.form.language')->createView()
        );
    }

    /**
     * Disable language
     *
     * @param Language $language
     *
     * @Route("/disable/{id}", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function disableAction(Language $language)
    {
        // Disable activated property
        $language->setActivated(false);
        $this->getLanguageManager()->getStorageManager()->persist($language);
        $this->getLanguageManager()->getStorageManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'Language successfully disable');

        return $this->redirect($this->generateUrl('pim_config_language_index'));
    }
}
