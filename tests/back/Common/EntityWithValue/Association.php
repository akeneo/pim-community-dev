<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\EntityWithValue;

final class Association
{
    /** @var string */
    private $type;

    /** @var array */
    private $products;

    /**
     * @param string $type
     * @param array  $products
     */
    public function __construct(string $type, array $products)
    {
        $this->type = $type;
        $this->products = $products;
    }

    /**
     * @param string $type
     * @param array  $products
     *
     * @return Association
     */
    public static function create(string $type, array $products): Association
    {
        return new self($type, $products);
    }

    /**
     * @return array
     */
    public function toStandardFormat(): array
    {
        return [
            $this->type => [
                'products' => $this->products,
            ]
        ];
    }
}
