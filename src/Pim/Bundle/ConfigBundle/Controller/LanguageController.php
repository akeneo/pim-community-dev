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
        $languages = $this->getLanguageRepository()->findAll();

        return array('languages' => $languages);
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    /**
     * Get language repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getLanguageRepository()
    {
        return $this->getEntityManager()->getRepository('PimConfigBundle:Language');
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
        $this->getEntityManager()->persist($language);
        $this->getEntityManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'Language successfully disable');

        return $this->redirect($this->generateUrl('pim_config_language_index'));
    }
}
