Feature: Validate localizable values of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for localizable attributes

Background:
  Given the following locales "en_US, fr_FR, es_ES"
  And the following "ecommerce" channel with locales "en_US"
  And the following "mobile" channel with locales "fr_FR"
  And the following attributes:
    | code        | type                   | scopable | localizable | available_locales |
    | sku         | pim_catalog_identifier | 0        | 0           |                   |
    | name        | pim_catalog_text       | 0        | 1           |                   |
    | description | pim_catalog_textarea   | 1        | 1           |                   |
    | gdpr        | pim_catalog_text       | 0        | 1           | fr_FR             |

  @acceptance-back
  Scenario: Providing an active locale should not raise an error
    When a product is created with values:
      | attribute | data        | locale |
      | name      | My product  | en_US  |
    Then no error is raised

  @acceptance-back
  Scenario: Providing a non erxistent locale should raise an error
    When a product is created with values:
      | attribute | data       | locale  |
      | name      | My product | non_EXI |
    Then the error 'Attribute "name" expects an existing and activated locale, "non_EXI" given.' is raised

  @acceptance-back
  Scenario: Providing an inactive locale should raise an error
    When a product is created with values:
      | attribute | data       | locale |
      | name      | My product | es_ES  |
    Then the error 'Attribute "name" expects an existing and activated locale, "es_ES" given.' is raised

  @acceptance-back
  Scenario: Providing a locale bound to the channel should not raise an error
    When a product is created with values:
      | attribute   | data       | scope  | locale |
      | description | My product | mobile | fr_FR  |
    Then no error is raised

  @acceptance-back
  Scenario: Providing a locale not bound to the channel should raise an error
    When a product is created with values:
      | attribute   | data       | scope     | locale  |
      | description | My product | ecommerce | fr_FR   |
    Then the error 'Attribute "description" expects a valid locale, "fr_FR" is not bound to channel "ecommerce".' is raised

  @acceptance-back
  Scenario: Providing a locale part of a locale specific attribute's available locales should not raise an error
    When a product is created with values:
      | attribute | data | locale |
      | gdpr      | test | fr_FR  |
    Then no error is raised

  @acceptance-back
  Scenario: Providing a locale which is not part of a locale specific attribute's available locales should raise an error
    When a product is created with values:
      | attribute | data | locale |
      | gdpr      | test | en_US  |
    Then the error '"en_US" is not part of the available locales for the locale specific attribute "gdpr".' is raised
