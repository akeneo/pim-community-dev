<?php

namespace Pim\Component\Connector\Validator\Constraints;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for the product export builder filter data.
 * Filter data are Product Query Build filters.
 *
 * This validator tries to apply filters to a PQB then catch errors
 * to build violations.
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilterDataValidator extends ConstraintValidator
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param TranslatorInterface                 $translator
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        TranslatorInterface $translator
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $pqb = $this->pqbFactory->create(['default_scope' => $value['structure']['scope']]);

        foreach ($value['data'] as $data) {
            try {
                $context = isset($data['context']) ? $data['context'] : [];
                $pqb->addFilter($data['field'], $data['operator'], $data['value'], $context);
            } catch (PropertyException $exception) {
                $this->context->buildViolation(
                        $this->translator->trans(
                            sprintf('pim_catalog.constraint.%s', $exception->getCode())
                        )
                    )
                    ->atPath(sprintf('[data][%s][%d]', $data['field'], 0))
                    ->addViolation();
            } catch (\LogicException $exception) {
                $this->context->buildViolation(sprintf('Missing attribute %s', $data['field']))
                    ->addViolation();
            }
        }
    }
}
