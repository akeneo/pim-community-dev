<?php

namespace Pim\Bundle\ConfigBundle\Controller;

use Pim\Bundle\ConfigBundle\Entity\Language;

use Symfony\Component\Locale\Locale;

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
        $language = new Language();

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
     * Remove language
     *
     * @param Language $language
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Language $language)
    {
        $manager = $this->getLanguageManager()->getStorageManager();
        $manager->remove($language);
        $manager->flush();

        $this->get('session')->getFlashBag()->add('success', 'Language successfully removed');

        return $this->redirect($this->generateUrl('pim_config_language_index'));
    }
}
