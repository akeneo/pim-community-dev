<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Validation\Constraints;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\CategorizeProductCommand;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\FillProductValuesCommand;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingCategoriesValidator extends ConstraintValidator
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($command, Constraint $constraint)
    {
        if (!$command instanceof CategorizeProductCommand) {
            throw new UnexpectedTypeException($constraint, CategorizeProductCommand::class);
        }

        if (!$constraint instanceof ExistingCategories) {
            throw new UnexpectedTypeException($constraint, ExistingCategories::class);
        }

        if (empty($command->categories())) {
            return;
        }

        $sql = <<<SQL
            SELECT 
                code
            FROM 
                pim_catalog_category c
            WHERE 
                c.code IN (:category_codes)
SQL;

        $categoryCodes = $command->categories();
        $existingCategories= $this->connection->executeQuery(
            $sql,
            [':category_codes' => $categoryCodes],
            [':category_codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll(PDO::FETCH_COLUMN, 0);

        $notExistingCode = array_diff($categoryCodes, $existingCategories);

        if (!empty($notExistingCode)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
