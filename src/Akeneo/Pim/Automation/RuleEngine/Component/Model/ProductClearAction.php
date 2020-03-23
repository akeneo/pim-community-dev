<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ProductClearAction implements ProductClearActionInterface
{
    /** @var string */
    private $field;

    /** @var string|null */
    private $locale;

    /** @var string|null */
    private $scope;

    public function __construct(array $data)
    {
        Assert::keyExists($data, 'field', 'The clear configuration requires a "field" key.');
        Assert::string($data['field'], 'The clear field configuration must be a string.');
        Assert::nullOrString($data['locale'] ?? null, 'The clear locale configuration must be a string or null.');
        Assert::nullOrString($data['scope'] ?? null, 'The clear scope configuration must be a string or null.');

        $this->field = strtolower($data['field']);
        $this->locale = $data['locale'] ?? null;
        $this->scope = $data['scope'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * {@inheritDoc}
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * {@inheritDoc}
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * {@inheritDoc}
     */
    public function getImpactedFields()
    {
        return [$this->field];
    }
}
