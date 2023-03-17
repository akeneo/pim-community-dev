<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToGenerateIdentifierFromNomenclature;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedNomenclatureException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureRepository;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PropertyProcessApplier
{
    public function __construct(
        private readonly NomenclatureRepository $nomenclatureRepository,
    ) {
    }

    public function apply(
        Process $process,
        string $nomenclatureProperty,
        string $code,
        string $target,
        string $prefix
    ): string {
        switch ($process->type()) {
            case Process::PROCESS_TYPE_TRUNCATE:
                Assert::integer($process->value());
                if ($process->operator() === Process::PROCESS_OPERATOR_EQ) {
                    try {
                        Assert::minLength($code, $process->value());
                    } catch (\InvalidArgumentException) {
                        throw new UnableToTruncateException(
                            \sprintf('%s%s', $prefix, $code),
                            $target,
                            $code
                        );
                    }
                }

                return \substr($code, 0, $process->value());
            case Process::PROCESS_TYPE_NOMENCLATURE:
                $nomenclature = $this->nomenclatureRepository->get($nomenclatureProperty);
                if (null === $nomenclature) {
                    throw new UndefinedNomenclatureException(
                        \sprintf('%s%s', $prefix, $code),
                        $target,
                        $nomenclatureProperty,
                    );
                }
                $values = $nomenclature->values();

                $value = null;
                if (isset($values[$code])) {
                    $value = $values[$code];
                } elseif ($nomenclature->generateIfEmpty()) {
                    $value = \substr($code, 0, $nomenclature->value());
                }
                if (null === $value) {
                    throw new UnableToGenerateIdentifierFromNomenclature(
                        \sprintf('%s%s', $prefix, $code),
                        $target,
                        $code,
                        $nomenclatureProperty,
                    );
                }
                if (\strlen($value) > $nomenclature->value()) {
                    throw new UnableToTruncateException(
                        \sprintf('%s%s', $prefix, $code),
                        $target,
                        $code
                    );
                }

                if (Process::PROCESS_OPERATOR_EQ === $nomenclature->operator() && \strlen($value) < $nomenclature->value()) {
                    throw new UnableToTruncateException(
                        \sprintf('%s%s', $prefix, $code),
                        $target,
                        $code
                    );
                }

                return $value;
            case Process::PROCESS_TYPE_NO:
            default:
                return $code;
        }
    }
}
