#  Client side form validation
## Setup validation rules for form fields
Main aim of development client side validation was to support same validation annotation which is used for server side - [Symfony validation](http://symfony.com/doc/current/book/validation.html). Once `validation.yml` is created, all rules get translated to fields `data-validation` attribute, e.g.:
```yml
Bundle\UserBundle\Entity\User:
    properties:
        username:
            - NotBlank:     ~
            - Length:
                min:        3
                max:        255
```
will be translated to
```html
<input name="user_form[username]"
    data-validation="{&quot;NotBlank&quot;:null,&quot;Length&quot;:{&quot;min&quot;:3,&quot;max&quot;:255}}">
```
This `data-validation` is supported by client side validation. Which is, by the way, extended version of popular [jQuery Validation Plugin](http://jqueryvalidation.org/).

## Validation rules
Client side validation method is RequireJS module, which should export an array with three values:
 1. Methods name
 2. Validation function
 3. Error message or function which defines message and returns it

Trivial validation rule module would look like:
```js
define(['underscore', 'oro/translator']
function (_, __) {
    'use strict';

    var defaultParam = {
        message: 'Invalid input value'
    };

    return [
        'ValidationMethodRule',

        /**
         * @param {string|undefined} value
         * @param {Element} element
         * @param {?Object} param
         * @this {jQuery.validator}
         * @returns {boolean|string}
         */
        function (value, element, param) {
            return true;
        },

        /**
         * @param {Object} param
         * @param {Element} element
         * @this {jQuery.validator}
         * @returns {string}
         */
        function (param, element) {
            param = _.extend({}, defaultParam, param);
            return __(param.message);
        }
    ]
});
```

## Loading custom validation rules
To load custom validator, just call `$.validator.loadMethod` with the name of RequireJS module, which exports validation method:
```js
$.validator.loadMethod('my/validation/method')
```
After it, form fields which have this constraint will be processed by this validation method.

## Conformity server side validations to client once
```
+--------------+---------+-----+------------------------+---------+
| Server side  | Symfony | Oro |       Client side      | Coment. |
+--------------+---------+-----+------------------------+---------+
| All          |    √    |     |                        |   (2)   |
| Blank        |    √    |     |                        |         |
| Callback     |    √    |     |                        |   (2)   |
| Choice       |    √    |     |                        |   (2)   |
| Collection   |    √    |     |                        |   (2)   |
| Count        |         |  √  | oro/validator/count    |   (1)   |
| Country      |    √    |     |                        |         |
| DateTime     |    √    |  √  |                        |         |
| Date         |    √    |  √  | oro/validator/date     |         |
| Email        |    √    |     | oro/validator/email    |         |
| False        |    √    |     |                        |         |
| File         |    √    |     |                        |   (2)   |
| Image        |    √    |     |                        |   (2)   |
| Ip           |    √    |     |                        |         |
| Language     |    √    |     |                        |         |
| Length       |    √    |     | oro/validator/length   |         |
| Locale       |    √    |     |                        |         |
| MaxLength    |    √    |     |                        |         |
| Max          |    √    |  √  |                        |         |
| MinLength    |    √    |     |                        |         |
| Min          |    √    |  √  |                        |         |
| NotBlank     |    √    |     | oro/validator/notblank |         |
| NotNull      |    √    |  √  |                        |   (2)   |
| Null         |    √    |     |                        |   (2)   |
| Range        |    √    |  √  | oro/validator/range    |         |
| Regex        |    √    |     | oro/validator/regex    |         |
| Repeated     |    √    |     |                        |         |
| SizeLength   |    √    |     |                        |         |
| Size         |    √    |  √  |                        |         |
| Time         |    √    |     |                        |         |
| True         |    √    |     |                        |         |
| Type         |    √    |     |                        |   (2)   |
| UniqueEntity |    √    |     |                        |         |
| Url          |    √    |     | oro/validator/url      |         |
+--------------+---------+-----+------------------------+---------+
```

 1. supports only group of checkboxes with same name (like `user[role][]`)
 2. can't be supported on client side
