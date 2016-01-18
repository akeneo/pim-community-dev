Feature: Disable attribute fields updated by a variant group
  In order to prevent from editing an attribute that will be overriden by a variant group
  As a product manager
  I need should not be able to edit an attributes owned by a variant group

  @javascript
  Scenario: Successfully display a readonly form for a product in a variant group
    Given the "default" catalog configuration
    And I add the "english" locale to the "mobile" channel
    And the following attributes:
      | code        | label       | type         | scopable | localizable | metric_family | default_metric_unit |
      | options     | Options     | multiselect  | yes      | no          |               |                     |
      | color       | Color       | simpleselect | no       | no          |               |                     |
      | name        | Name        | text         | no       | yes         |               |                     |
      | description | Description | textarea     | no       | no          |               |                     |
      | dimension   | Dimension   | number       | yes      | yes         |               |                     |
      | price       | Price       | prices       | no       | no          |               |                     |
      | length      | Length      | metric       | no       | no          | Length        | CENTIMETER          |
    And the following "options" attribute options: Blue
    And the following "color" attribute options: Red, black and Green
    And the following product groups:
      | code          | label          | axis  | type    |
      | tshirt_akeneo | Akeneo T-Shirt | color | VARIANT |
    And the following variant group values:
      | group         | attribute   | value            | locale | scope     |
      | tshirt_akeneo | name        | Great sneakers   | fr_FR  |           |
      | tshirt_akeneo | dimension   | 12               | en_US  | ecommerce |
      | tshirt_akeneo | options     | blue             |        | mobile    |
      | tshirt_akeneo | description | nice description |        |           |
      | tshirt_akeneo | price-EUR   | 10.0             |        |           |
      | tshirt_akeneo | length      | 15.0 CENTIMETER  |        |           |
    And the following products:
      | sku  | color | groups        |
      | sku1 | red   | tshirt_akeneo |
    And I am logged in as "Julia"
    When I am on the "sku1" product page
    And I switch the scope to "mobile"
    Then the fields Options, Dimension, Price in EUR, Length should be disabled
    Given I switch the locale to "fr_FR"
    Then the field name should be disabled
