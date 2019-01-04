<?php
    
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class MatchingPublicAndPrivateCertificates extends Constraint
{
    /** @var string */
    public $message;

    /** @var string */
    public $publicCertificatePropertyName;

    /** @var string */
    public $privateCertificatePropertyName;

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
