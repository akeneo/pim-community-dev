Overview
--------

Datagird backend consists of several entities, which are used to perform specific actions.
Every entity implements interface, so every part can be easy extended and replaced with external component.

Datagrid entities use standard Symfony interfaces to perform translation and validation.

#### Used External Interfaces

**Symfony**

* Translator - Symfony\Component\Translation\TranslatorInterface;
* Validator - Symfony\Component\Validator\ValidatorInterface;
