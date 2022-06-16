<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualUploadStorage;
use PhpSpec\ObjectBehavior;

class ManualUploadStorageHydratorSpec extends ObjectBehavior
{
    public function it_supports_only_manual_upload_storage()
    {
        $this->supports(['type' => 'manual_upload', 'file_path' => 'a_file_path'])->shouldReturn(true);
        $this->supports(['type' => 'none'])->shouldReturn(false);
        $this->supports(['type' => 'unknown'])->shouldReturn(false);
    }

    public function it_hydrates_a_manual_upload_storage()
    {
        $this->hydrate(['type' => 'manual_upload', 'file_path' => 'a_file_path'])->shouldBeLike(new ManualUploadStorage('a_file_path'));
    }
}
