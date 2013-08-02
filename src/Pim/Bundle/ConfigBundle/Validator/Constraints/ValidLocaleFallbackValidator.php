<?php
namespace Pim\Bundle\ConfigBundle\Validator\Constraints;

use Pim\Bundle\ConfigBundle\Entity\Locale;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Locale fallback validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidLocaleFallbackValidator extends ConstraintValidator
{

    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Validate the locale fallback
     *
     * @param Locale     $locale     Locale entitty
     * @param Constraint $constraint Validator constraint
     */
    public function validate($locale, Constraint $constraint)
    {
        if ($locale->getFallback()) {
            $usedAsFallback = $this->em->getRepository('PimConfigBundle:Locale')
                                   ->findBy(array('fallback' => $locale->getCode()));
            if ($usedAsFallback) {
                $this->context->addViolationAtSubPath('fallback', $constraint->fallbackNotAllowed, array(), null);

                return;
            }

            if ($locale->getCode() === $locale->getFallback()) {
                $this->context->addViolationAtSubPath('fallback', $constraint->fallbackTwinLocale, array(), null);
            }
        }
    }
}
