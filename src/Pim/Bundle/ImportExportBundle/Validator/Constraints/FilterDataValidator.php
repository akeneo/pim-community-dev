<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\ExceptionTranslationProvider;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilterDataValidator extends ConstraintValidator
{
    /** @var ProductQueryBuilderFactory */
    protected $pqbFactory;

    /** @var ExceptionTranslationProvider */
    protected $translationProvider;

    /**
     * @param ProductQueryBuilderFactory   $pqbFactory
     * @param ExceptionTranslationProvider $translationProvider
     */
    public function __construct(
        ProductQueryBuilderFactory $pqbFactory,
        ExceptionTranslationProvider $translationProvider
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->translationProvider = $translationProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $pqb = $this->pqbFactory->create();

        foreach ($value as $data) {
            try {
                $pqb->addFilter($data['field'], $data['operator'], $data['value']);
            } catch (InvalidArgumentException $e) {
                $this->context->buildViolation($this->translationProvider->getTranslation($e))
                    ->atPath($data['field'])
                    ->addViolation();
            }
        }
    }
}
