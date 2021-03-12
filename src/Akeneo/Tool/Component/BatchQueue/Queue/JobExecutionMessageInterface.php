<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface JobExecutionMessageInterface
{
    public function getId(): ?int;

    public function getJobExecutionId(): ?int;

    public function getConsumer(): ?string;

    public function consumedBy(string $consumer): void;

    public function getCreateTime(): \DateTime;

    public function getUpdatedTime(): ?\DateTime;

    public function getOptions(): array;
}
