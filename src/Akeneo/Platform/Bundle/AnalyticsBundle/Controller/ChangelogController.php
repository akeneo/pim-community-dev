<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Controller;

use Akeneo\Tool\Component\Analytics\Changelog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    JM Leroux <jmleroux.pro@gmail.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangelogController extends Controller
{
    /** @var string[] */
    private $changelogDirectories;

    /**
     * @param string[] $changelogDirectories
     */
    public function __construct(array $changelogDirectories)
    {
        $this->changelogDirectories = $changelogDirectories;
    }

    /**
     * @return Response
     */
    public function indexAction()
    {
        $changelog = new Changelog();
        foreach ($this->changelogDirectories as $edition => $changelogFolder) {
            foreach (glob($changelogFolder . '/CHANGELOG-*.md') as $changelogFile) {
                $changelog->parseFile($changelogFile, $edition);
            }
        }

        $changelog->sortByDate();

        return new JsonResponse($changelog->normalize());
    }
}
