- Create folder structure inside tests/back for each bounded context
- Move Acceptance Behat contexts into those bounded context folders
- Common: Move all repos and builder inside Common
- Common: Regroup by technical pattern (repo, builder...) inside Common
- Integration: Drop the IntegrationTestsBundle
- Create a Common/Integration folder to move all stuff inside
- Create folder structure inside tests/features for each bounded context
- namespace for non spec: AkeneoTest\UserManagement\Integration\Updater
- namespace for specs:  
    namespace AkeneoTest\UserManagement\Specification\Bundle\Context;
    namespace Specification\Akeneo\UserManagement\Bundle\Context;
    namespace spec\Akeneo\UserManagement\Bundle\Context;
    
