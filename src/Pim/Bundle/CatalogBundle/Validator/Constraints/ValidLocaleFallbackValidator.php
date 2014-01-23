<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Doctrine\ORM\EntityManager;

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
            $usedAsFallback = $this->em->getRepository('PimCatalogBundle:Locale')
                                   ->findBy(['fallback' => $locale->getCode()]);
            if ($usedAsFallback) {
                $this->context->addViolationAt('fallback', $constraint->fallbackNotAllowed, [], null);

                return;
            }

            if ($locale->getCode() === $locale->getFallback()) {
                $this->context->addViolationAt('fallback', $constraint->fallbackTwinLocale, [], null);
            }
        }
    }
}
