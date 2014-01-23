<?php

namespace Pim\Bundle\JsFormValidationBundle\Twig\Extension;

use Symfony\Component\Form\FormView;
use Symfony\Component\DependencyInjection\ContainerInterface;
use APY\JsFormValidationBundle\Twig\Extension\JsFormValidationTwigExtension as APYJsFormValidationTwigExtension;

/**
 * Override the form validation twig extension to render the form validation script inline
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsFormValidationTwigExtension extends APYJsFormValidationTwigExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = parent::getFunctions();
        $functions['JSFV'] = new \Twig_Function_Method(
            $this,
            'jsFormValidationFunction',
            ['is_safe' => ['all']]
        );

        return $functions;
    }

    /**
     * Retrieves validation javascript as an inline script
     *
     * @param FormView $formView
     * @param boolean  $getPlainScript
     *
     * @return string
     */
    public function jsFormValidationFunction(FormView $formView, $getPlainScript = false)
    {
        if ($this->container->getParameter('apy_js_form_validation.enabled')) {
            // Generate the script
            $jsfvGenerator = $this->container->get('jsfv');
            $script = $jsfvGenerator->generate($formView);

            if ($getPlainScript) {
                return $script;
            } else {
                return sprintf('<script type="text/javascript">%s</script>', $script);
            }
        } else {
            return '';
        }
    }
}
