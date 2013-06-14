<?php
namespace Pim\Bundle\ConfigBundle\Tests\Unit\Form\Type;

use Symfony\Component\Yaml\Yaml;

use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;

use Symfony\Component\Form\Forms;

use Pim\Bundle\ConfigBundle\Form\Type\LocaleType;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleTypeTest extends TypeTestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // redefine form factory
        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(
                new FormTypeValidatorExtension(
                    $this->getMock('Symfony\Component\Validator\ValidatorInterface')
                )
            )
            ->getFormFactory();

        // initialize locale configuration
        $config = $this->initializeConfiguration();
    }

    /**
     * Initialize locale configuration
     * @return config
     */
    protected function initializeConfiguration()
    {
        $filepath = realpath(dirname(__FILE__) .'/../../../../Resources/config') .'/pim_locales.yml';

        if (!file_exists($filepath)) {
            throw new \Exception($filepath .' not exists');
        }

        return Yaml::parse($filepath);
    }

    /**
     * Data provider for success validation of form
     * @return multitype:multitype:multitype:mixed
     *
     * @static
     */
    public static function successProvider()
    {
        return array(
            array(array('id' => 5, 'code' => 'en_US', 'fallback' => 'en', 'activated' => true)),
            array(array('id' => null, 'code' => 'fr_CH', 'fallback' => 'fr', 'activated' => true))
        );
    }
}
