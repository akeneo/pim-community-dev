Email templates
==============

Any bundle can define it's own templates using Data Fixtures.
To achieve this - add a fixture in SomeBundle\DataFixtures\ORM folder that extends Oro\Bundle\EmailBundle\DataFixtures\ORM\AbstractEmailFixture
abstract class and implements the only method - getEmailsDir:
``` php
class DataFixtureName extends AbstractEmailFixture
{
    /**
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../data/emails';
    }
}
```
Place email templates in that defined folder with any file name.

Email format
------------
It's possible to define email format based on file name, e.g.:

 - html format: update_user.html.twig, some_name.html
 - txt format: some_name.txt.twig, some_name.txt
 - default format - html, if file extension can't be recognized as html or txt

Email parameters
-----------------
Each template must define these params:

 - entityName - each template knows how to display some entity
 - subject - email subject

Optional parameter:

 - name - template name; the template file name without extension is used if this parameter is not specified 
 - isSystem - 1 or 0, default - false (0)
 - isEditable - 1 or 0, default - false (0); make sense only if isSystem = 1 and allow to edit content of system templates

Params defined with syntax at the top of the template
```
@entityName = Oro\Bundle\UserBundle\Entity\User
@subject = Subject {{ entity.username }}
@isSystem = 1
```

Available email variables
-------------------------
Each email template have some set of variables that are defined in the current scope and depends on entityName.

 - entity - object of entityName class with all it's fields

All fields that available in email templates stored in EntityConfigBundle, and can be configured in UI
