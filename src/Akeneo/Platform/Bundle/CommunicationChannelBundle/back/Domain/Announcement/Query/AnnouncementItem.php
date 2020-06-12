<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AnnouncementItem
{
    public const TITLE = 'title';

    public const DESCRIPTION = 'description';

    public const IMAGE = 'img';

    public const ALT_IMAGE = 'altImg';

    public const LINK = 'link';

    public const START_DATE = 'startDate';

    public const NOTIFICATION_DURATION = 'notificationDuration';

    public const TAGS = 'tags';

    public const EDITIONS = 'editions';

    public $title;
}
