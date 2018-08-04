<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Validation\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Product;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingChannelsInValuesValidator extends ConstraintValidator
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
    public function validate($product, Constraint $constraint)
    {
        if (!$product instanceof Product) {
            throw new UnexpectedTypeException($constraint, Product::class);
        }

        if (!$constraint instanceof ExistingChannelsInValues) {
            throw new UnexpectedTypeException($constraint, ExistingChannelsInValues::class);
        }

        if (null === $product->values()) {
            return;
        }

        $sql = <<<SQL
            SELECT 
                code
            FROM 
                pim_catalog_channel c
            WHERE 
                c.code IN (:channel_codes)
SQL;

        $channelCodes = $this->getChannelCodes($product);
        $existingLocales = $this->connection->executeQuery(
            $sql,
            ['channel_codes' => $channelCodes],
            ['channel_codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll(PDO::FETCH_COLUMN, 0);

        $notExistingCode = array_diff($channelCodes, $existingLocales);

        if (!empty($notExistingCode)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function getChannelCodes(Product $product): array
    {
        $channels = [];
        foreach ($product->values()->all() as $value) {
            if (null !== $value->channelCode()) {
                $channels[$value->channelCode()] = $value->channelCode();
            }
        }

        return $channels;
    }
}
