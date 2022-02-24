<?php


namespace AkeneoTest\Tool\Integration\Logging\src;

use Akeneo\Tool\Bundle\LoggingBundle\Domain\Model\AuditLog;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TestServiceFixture
{
    public const SOMETHING_PUBLIC_DONE = "Something public done";
    public const SOMETHING_PROTECTED_DONE = "Something protected done";

    #[AuditLog]
    public function doSomethingPublic(): string
    {
        return self::SOMETHING_PUBLIC_DONE;
    }

    public function callSomeProtectedMethod(): string {
        return $this->doSomethingProtected();
    }

    #[AuditLog]
    public function callErroredMethod() {
        throw new \Error("Error thrown during callErroredMethod");
    }

    #[AuditLog]
    protected function doSomethingProtected(): string
    {
        return self::SOMETHING_PROTECTED_DONE;
    }

}