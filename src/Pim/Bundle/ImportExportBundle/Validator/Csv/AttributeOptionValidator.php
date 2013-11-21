<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Csv;

use Symfony\Component\Validator\ValidatorInterface;

/**
 * Validates a csv option row
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionValidator implements RowValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Constructor
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $data)
    {
        var_dump($data);
    }
}
