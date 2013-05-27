Overview
--------

Datagird backend consists of several entities, which are used to perform specific actions. Every entity implements interface, so every part can be easy extended and replaced with external component.

Datagrid entities use standard Symfony interfaces to perform translation and validation. Also some interfaces and entities are extended from Sonata AdminBundle classes, so basic Sonata classes can be injected into datagrid entities.

#### Used External Interfaces

**Symfony**

* Translator - Symfony\Component\Translation\TranslatorInterface;
* Validator - Symfony\Component\Validator\ValidatorInterface;

**Sonata AdminBundle**

* Datagrid - Sonata\AdminBundle\Datagrid\DatagridInterface;
* Filter - Sonata\AdminBundle\Filter\FilterInterface;
* Filter Factory - Sonata\AdminBundle\Filter\FilterFactoryInterface;
* Pager - Sonata\AdminBundle\Datagrid\PagerInterface;
* Proxy Query - Sonata\AdminBundle\Datagrid\ProxyQueryInterface.
