<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Validation\EnrichedEntity;

use PHPUnit\Framework\TestCase;

class RawDataValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_violations_if_identifier_is_invalid()
    {
        $validator = new RawDataValidator();

        $data = ['labels' => []];
        $violations = $validator->validate($data);
        $this->assertGreaterThan(0, $violations->count());

        $data = ['identifier' => null, 'labels' => []];
        $violations = $validator->validate($data);
        $this->assertGreaterThan(0, $violations->count());

        $data = ['identifier' => '', 'labels' => []];
        $violations = $validator->validate($data);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function it_returns_violations_if_labels_are_invalid()
    {
        $validator = new RawDataValidator();
    }
}
