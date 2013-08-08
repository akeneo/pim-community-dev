<?php

namespace Oro\Bundle\EmailBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Validator\Constraints\VariablesConstraint;

class VariablesValidator extends ConstraintValidator
{
    /** @var \Twig_Environment */
    protected $twig;

    /** @var SecurityContextInterface */
    protected $securityContext;

    public function __construct(\Twig_Environment $twig, SecurityContextInterface $securityContext)
    {
        $this->twig = $twig;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($emailTemplate, Constraint $constraint)
    {
        /** @var EmailTemplate $emailTemplate */
        /** @var VariablesConstraint $constraint */

        $fieldsToValidate = array(
            'subject' => $emailTemplate->getSubject(),
            'content' => $emailTemplate->getContent(),
        );

        foreach ($emailTemplate->getTranslations() as $trans) {
            if (in_array($trans->getField(), array('subject', 'content'))) {
                $fieldsToValidate[$trans->getLocale() . '.' . $trans->getField()] = $trans->getContent();
            }
        }

        $className = class_exists($emailTemplate->getEntityName()) ? $emailTemplate->getEntityName() : false;
        $relatedEntity = $className ? new $className() : false;

        $errors = array();
        foreach ($fieldsToValidate as $field => $value) {
            try {
                $this->twig->render(
                    $value,
                    array(
                        'entity' => $relatedEntity,
                        'user'   => $this->getUser()
                    )
                );
            } catch (\Exception $e) {
                $errors[$field] = true;
            }
        }

        if (!empty($errors)) {
            $this->context->addViolation($constraint->message);
        }
    }

    /**
     * Return current user
     *
     * @return UserInterface|bool
     */
    private function getUser()
    {
        return $this->securityContext->getToken() && !is_string($this->securityContext->getToken()->getUser())
            ? $this->securityContext->getToken()->getUser() : false;
    }
}
