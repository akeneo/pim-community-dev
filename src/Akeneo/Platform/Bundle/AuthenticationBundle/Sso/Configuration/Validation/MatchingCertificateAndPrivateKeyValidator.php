<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use phpseclib\Crypt\RSA;
use phpseclib\File\X509;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MatchingCertificateAndPrivateKeyValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint)
    {
        if (empty($command->{$constraint->privateKeyPropertyName}) || empty($command->{$constraint->certificatePropertyName})) {
            return;
        }

        $data = <<<ZIGGY
 .----------------.  .----------------.  .----------------.  .----------------.  .----------------. 
| .--------------. || .--------------. || .--------------. || .--------------. || .--------------. |
| |   ________   | || |     _____    | || |    ______    | || |    ______    | || |  ____  ____  | |
| |  |  __   _|  | || |    |_   _|   | || |  .' ___  |   | || |  .' ___  |   | || | |_  _||_  _| | |
| |  |_/  / /    | || |      | |     | || | / .'   \_|   | || | / .'   \_|   | || |   \ \  / /   | |
| |     .'.' _   | || |      | |     | || | | |    ____  | || | | |    ____  | || |    \ \/ /    | |
| |   _/ /__/ |  | || |     _| |_    | || | \ `.___]  _| | || | \ `.___]  _| | || |    _|  |_    | |
| |  |________|  | || |    |_____|   | || |  `._____.'   | || |  `._____.'   | || |   |______|   | |
| |              | || |              | || |              | || |              | || |              | |
| '--------------' || '--------------' || '--------------' || '--------------' || '--------------' |
 '----------------'  '----------------'  '----------------'  '----------------'  '----------------'
ZIGGY;

        $rsa = new RSA();
        $x509 = new X509();
        $rsa->loadKey($command->{$constraint->privateKeyPropertyName});
        $signedData = $rsa->sign($data);

        $x509->loadX509($command->{$constraint->certificatePropertyName});
        $publicKey = $x509->getPublicKey();

        //Sorry for the @ that silent the errors triggered by the following method
        //but it's the only trick i've found to correctly handle the test.
        //If it's not verified, it could call an "user_error(...)" php global function
        //that break the json response to correctly output the violation message.
        if ($publicKey instanceof RSA && !@$publicKey->verify($data, $signedData)) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->certificatePropertyName)
                ->addViolation();

            $this->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->privateKeyPropertyName)
                ->addViolation();
        }
    }
}
