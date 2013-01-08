<?php

namespace Oro\Bundle\UiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/1column")
     * @Template()
     */
    public function oneColumnAction()
    {
        return array();
    }
    /**
     * @Route("/1column-with-toolbar")
     * @Template()
     */
    public function oneColumnWithToolbarAction()
    {
        return array();
    }
    /**
     * @Route("/2columns-left")
     * @Template()
     */
    public function twoColumnLeftAction()
    {
        return array();
    }
    /**
     * @Route("/2columns-right")
     * @Template()
     */
    public function twoColumnRightAction()
    {
        return array();
    }
    /**
     * @Route("/3column")
     * @Template()
     */
    public function threeColumnAction()
    {
        return array();
    }
    /**
     * @Route("/forgot-password")
     * @Template()
     */
    public function forgotPasswordAction()
    {
        return array();
    }
    /**
     * @Route("/login-page")
     * @Template()
     */
    public function loginPageAction()
    {
        return array();
    }
    /**
     * @Route("/registration-page")
     * @Template()
     */
    public function registrationPageAction()
    {
        return array();
    }
    /**
     * @Route("/404")
     * @Template()
     */
    public function Page404Action()
    {
        return array();
    }
    /**
     * @Route("/503")
     * @Template()
     */
    public function Page503Action()
    {
        return array();
    }
    /**
     * @Route("/form-elements")
     * @Template()
     */
    public function formElementsAction()
    {
        return array();
    }
    /**
     * @Route("/system-messages")
     * @Template()
     */
    public function systemMessagesAction()
    {
        return array();
    }
    /**
     * @Route("/buttons-page")
     * @Template()
     */
    public function buttonsPageAction()
    {
        return array();
    }
    /**
     * @Route("/tables-pege")
     * @Template()
     */
    public function tablesPageAction()
    {
        return array();
    }
    /**
     * @Route("/general-elements")
     * @Template()
     */
    public function generalElementsAction()
    {
        return array();
    }
    /**
     * @Route("/dialog-page")
     * @Template()
     */
    public function dialogPageAction()
    {
        return array();
    }
    /**
     * @Route("/dialog-styled-page")
     * @Template()
     */
    public function dialogStyledPageAction()
    {
        return array();
    }
}
