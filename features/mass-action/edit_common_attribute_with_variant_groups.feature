@javascript
Feature: Disable attribute fields in mass edit if it comes from a variant group
  In order to prevent from overriding the value coming from a variant group
  As a product manager
  I need should not be able to edit an attributes owned by a variant group in the selection of the mass edit

  Scenario: Allow editing only common attributes but filter on attributes in variant groups
    Given the "default" catalog configuration
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
    And I am on the products page
    And I mass-edit products sku1
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Color in group "Other"
    And I should see "They are not available for mass edition: name, dimension, options, description, price, length."
