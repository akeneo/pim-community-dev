<?php

namespace Pim\Bundle\JsFormValidationBundle\Generator;

use Symfony\Component\Form\FormView;
use Oro\Bundle\JsFormValidationBundle\Generator\FormValidationScriptGenerator as OroFormValidationScriptGenerator;

/**
 * Override the form validation script generator to force file regeneration by default
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormValidationScriptGenerator extends OroFormValidationScriptGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate(FormView $formView, $overwrite = true)
    {
        // Prepare output file
        $scriptPath = $this->container->getParameter('apy_js_form_validation.script_directory');
        $scriptRealPath = $this->container->getParameter('assetic.write_to').'/'.$scriptPath;
        $formName = isset($formView->vars['name']) ? $formView->vars['name'] : 'form';
        $scriptFile = strtolower($this->container->get('request')->get('_route')) . "_" . strtolower($formName) . ".js";

        if ($overwrite || false === file_exists($scriptRealPath . $scriptFile)) {
            $this->generateFile($formView, $scriptRealPath, $scriptFile);
        }

        return $this->container->get('templating.helper.assets')->getUrl($scriptPath.$scriptFile);
    }
}
