<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Validator;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Entity\EmailTemplateTranslation;
use Oro\Bundle\EmailBundle\Validator\VariablesValidator;
use Oro\Bundle\EmailBundle\Validator\Constraints\VariablesConstraint;

class VariablesValidatorTest extends \PHPUnit_Framework_TestCase
{
    const TEST_SUBJECT       = 'testSubject';
    const TEST_TRANS_SUBJECT = 'testTransSubject';
    const TEST_CONTENT       = 'testContent';

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $twig;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $securityContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $user;

    /** @var EmailTemplate */
    protected $template;

    /** @var VariablesValidator */
    protected $validator;

    /** @var VariablesConstraint */
    protected $variablesConstraint;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $context;

    public function setUp()
    {
        $this->twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()->getMock();
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $token = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface'
        );
        $this->user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()->getMock();
        $token->expects($this->any())->method('getUser')
            ->will($this->returnValue($this->user));
        $this->securityContext->expects($this->any())->method('getToken')
            ->will($this->returnValue($token));
        $this->context = $this->getMockForAbstractClass('Symfony\Component\Validator\ExecutionContextInterface');

        $this->template = new EmailTemplate();
        $this->variablesConstraint = new VariablesConstraint();

        $this->validator = new VariablesValidator($this->twig, $this->securityContext);
        $this->validator->initialize($this->context);
    }

    public function tearDown()
    {
        unset($this->twig);
        unset($this->securityContext);
        unset($this->user);
        unset($this->template);
        unset($this->validator);
        unset($this->variablesConstraint);
        unset($this->context);
    }

    public function testValidateNotErrors()
    {
        $this->template->setContent(self::TEST_CONTENT)
            ->setSubject(self::TEST_SUBJECT);
        $this->template->setEntityName('Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity\SomeEntity');

        $phpUnit = $this;
        $user = $this->user;
        $callback = function ($template, $params) use ($phpUnit, $user) {
            $phpUnit->assertInternalType('string', $template);

            $phpUnit->assertArrayHasKey('entity', $params);
            $phpUnit->assertArrayHasKey('user', $params);

            $phpUnit->assertInstanceOf(
                'Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity\SomeEntity',
                $params['entity']
            );
            $phpUnit->assertInstanceOf(get_class($user), $params['user']);
        };
        $this->twig->expects($this->at(0))->method('render')->with(self::TEST_SUBJECT)
            ->will($this->returnCallback($callback));
        $this->twig->expects($this->at(1))->method('render')->with(self::TEST_CONTENT)
            ->will($this->returnCallback($callback));

        $this->context->expects($this->never())->method('addViolation');

        $this->validator->validate($this->template, $this->variablesConstraint);
    }

    public function testValidateErrors()
    {
        $trans = new EmailTemplateTranslation();
        $trans->setField('subject')
            ->setContent(self::TEST_TRANS_SUBJECT);
        $this->template->setContent(self::TEST_CONTENT)
            ->setSubject(self::TEST_SUBJECT)
            ->getTranslations()->add($trans);

        $this->twig->expects($this->at(0))->method('render')->with(self::TEST_SUBJECT);
        $this->twig->expects($this->at(1))->method('render')->with(self::TEST_CONTENT);
        $this->twig->expects($this->at(2))->method('render')->with(self::TEST_TRANS_SUBJECT)
            ->will($this->throwException(new \Exception()));

        $this->context->expects($this->once())->method('addViolation')->with($this->variablesConstraint->message);

        $this->validator->validate($this->template, $this->variablesConstraint);
    }
}
