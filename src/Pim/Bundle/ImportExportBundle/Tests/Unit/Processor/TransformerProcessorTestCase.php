<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

/**
 * Test case for transformer based processors
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class TransformerProcessorTestCase extends \PHPUnit_Framework_TestCase
{
    protected $validator;
    protected $translator;

    protected function setUp()
    {
        $this->validator = $this->getMock('Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface');
        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $this->translator
            ->expects($this->any())
            ->method('trans')
            ->will(
                $this->returnCallback(
                    function ($message, array $parameters = array()) {
                        return sprintf(
                            '<tr>%s</tr>',
                            strtr($message, $parameters)
                        );
                    }
                )
            );
    }
}
