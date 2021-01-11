# 5.0.x

# 5.0.1 (2021-01-08)

# 5.0.0 (2020-12-31)

## Bug fixes

- PIM-9617: Configure clean_removed_attribute_job to be run on a single daemon

## Improvements

- PIM-9619: Improve error message when creating a new project with a name already used

## New features

## BC Breaks

- Replace `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface` by `Twig\Environment` in all codebase
- Replace `Symfony\Component\Translation\TranslatorInterface` by `Symfony\Contracts\Translation\TranslatorInterface` in all codebase
- Change parameter of `UnknownUserExceptionListener::onKernelException()` method from `Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent` to ` Symfony\Component\HttpKernel\Event\ExceptionEvent`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ProductDraftController` to remove `Symfony\Component\Translation\TranslatorInterface $translator` parameter
