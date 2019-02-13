#!/bin/sh

set -eu

if [[ -z "$1}" ]]; then
    echo "Provide the argument SUITES you want to list-tests as first argument"
fi

SUITES=$@

# Here is the output of phpunit

# it gives a header + The namespaces of the test and the test so as we launch by file we have to work a little bit on it

#PHPUnit 6.5.3 by Sebastian Bergmann and contributors.
#
#Available test(s):
# - Akeneo\Bundle\RuleEngineBundle\tests\integration\ExecuteRuleWithoutPermissionsAppliedIntegration::testRuleExecutionOnAllProducts
# - tests\integration\Akeneo\Bundle\RuleEngineBundle\Normalizer\Standard\RuleIntegration::testRule
# - PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset\CreateAssetIntegration::testCreationOfAnAsset
# - PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset\CreateAssetIntegration::testResponseWhenContentIsEmpty
# - PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset\CreateAssetIntegration::testResponseWhenValidationFailed

vendor/bin/phpunit -c app --testsuite=$SUITES --list-tests | tail -n +4 | cut -c4- | cut -d ':' -f 1 | sort | uniq | sed -e 's/\\/\\\\/g'
