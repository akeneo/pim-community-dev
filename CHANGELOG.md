## Bug fixes

- AOB-1277: Fix issue when helm remove disk too fast on delete instance
- PIM-9742: Asset. Product link rules "Failed" without informations instead of sending Warning.
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
- PIM-9664: Display Ziggy as asset image when the preview cannot be generated
- PIM-9702: Fix infinite loop when using get all assets API endpoint
- PIM-9720: Fix Asset cursor search after query
- PIM-9723: Fix Mysql memory size issue during ordering in SQL when getting category trees in product grid
- PIM-9722: Fix the increasing amount of requests when editing attribute with options
- PIM-9698: Fix product and product model deletion events when entity does not have any category
- PIM-9710: Fix rule execution job status stuck in STARTED or STOPPING 
- PIM-9731: Fix proposals on boolean attributes when comparing an empty and a false value
- PIM-9756: Use PNG format for Asset transformations targets
- PIM-9760: Fix link assets to products job not translated in job tracker
- PIM-9693: Fix Asset creation modal failing when no label
- PIM-9755: Hide product model proposal changes that are already reviewed
- PIM-9764: Fix open Asset in new tab in Asset Manager library
- PIM-9767: Fix minimum & maximum user password validation
- PIM-9768: Handle error when trying to delete a product model with at least 1 variant already published
- PIM-9790: Fix memory leak on rules execution
- PIM-9788: Fix locales list on rule concatenate action
- PIM-9791: Fix memory issue on proposals widget
- PIM-9682: Link to proposal product model is completely wrong
- PIM-9800: Fix event not sent issue when creating products or product models

## Improvements

- PIM-9619: Improve error message when creating a new project with a name already used 
- PLG-45: Activate SSO authentication from a command CLI
- RAC-509: Upgrade asset limit by asset family to 10 millions

## New features

- DAPI-1443: Add possibility to export products depending on their Quality Score

## BC Breaks

- Replace `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface` by `Twig\Environment` in all codebase
- Replace `Symfony\Component\Translation\TranslatorInterface` by `Symfony\Contracts\Translation\TranslatorInterface` in all codebase
- Change parameter of `UnknownUserExceptionListener::onKernelException()` method from `Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent` to ` Symfony\Component\HttpKernel\Event\ExceptionEvent`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ProductDraftController` to remove `Symfony\Component\Translation\TranslatorInterface $translator` parameter
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleSelector` to remove `ProductRepositoryInterface $repo`
- Remove class `Akeneo/AssetManager/back/Domain/Event/AssetFamilyAssetsDeletedEvent`
- Update `Akeneo/AssetManager/back/Domain/Repository/AssetIndexerInterface.php` to:
    - remove the `removeByAssetFamilyIdentifier` method

