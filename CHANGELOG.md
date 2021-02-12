## Bug fixes

- PIM-9675: Api search_after on asset issue for Serenity clients
- PIM-9617: Configure clean_removed_attribute_job to be run on a single daemon
- PIM-9629: Fix filtering issue on product value "identifier" via the API for published products
- PIM-9640: Fix asset and record imports in XLSX when sone cells contain only numeric characters
- PIM-9646: Make the rule engine execution permission agnostic
- PIM-9649: Fix PDF product renderer disregarding permissions on Attribute groups
- PIM-9651: Concatenate rule does not keep anymore the trailing zeros on a decimal number
- PIM-9654: Allow single quote in DQI Word Dictionary
- PIM-9655: Fix multiple spellcheck calls on multi-select attributes
- PIM-9671: Do not process spell checking on attributes with data quality analysis disabled on group
- PIM-9659: Asset Manager - Fix missing warning message when changing page via the breadcrumb with unsaved changes.
- PIM-9676: Reference Entities - Fix missing warning message when changing page via the breadcrumb with unsaved changes.
- PIM-9668: Asset attribute - Fix Text area + Rich text editor modes turn text attributes into infinite extendable fields when not using spaces

## Improvements

- PIM-9619: Improve error message when creating a new project with a name already used 
- PLG-45: Activate SSO authentication from a command CLI
- RAC-509: Upgrade asset limit by asset family to 10 millions

## New features

## BC Breaks

- Replace `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface` by `Twig\Environment` in all codebase
- Replace `Symfony\Component\Translation\TranslatorInterface` by `Symfony\Contracts\Translation\TranslatorInterface` in all codebase
- Change parameter of `UnknownUserExceptionListener::onKernelException()` method from `Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent` to ` Symfony\Component\HttpKernel\Event\ExceptionEvent`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ProductDraftController` to remove `Symfony\Component\Translation\TranslatorInterface $translator` parameter
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleSelector` to remove `ProductRepositoryInterface $repo`
- Remove class `Akeneo/AssetManager/back/Domain/Event/AssetFamilyAssetsDeletedEvent`
- Update `Akeneo/AssetManager/back/Domain/Repository/AssetIndexerInterface.php` to:
    - remove the `removeByAssetFamilyIdentifier` method

