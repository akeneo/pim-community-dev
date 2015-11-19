@javascript
Feature: Edit a product with localized attributes
  In order to enrich the catalog
  As a regular user
  I need to be able to view, edit and save a product with localizes values

  Background:
    Given the "default" catalog configuration
    And the following attribute groups:
      | code    | label-en_US |
      | general | General     |
    And the following attributes:
      | code   | label-fr_FR | type   | scopable | unique | negative_allowed | decimals_allowed | group | metric_family | default_metric_unit |
      | number | Nombre      | number | no       | no     | yes              | yes              | other |               |                     |
      | weight | Poids       | metric | no       | no     | yes              | yes              | other | Weight        | GRAM                |
      | price  | Prix        | prices | no       | no     | no               | yes              | other |               |                     |
    And the following family:
      | code | label-en_US | attributes                 |
      | baz  | Baz         | sku, number, weight, price |
    And the following products:
      | sku | family | number  | weight        | price-EUR |
      | foo | baz    | -12.5   | 150.8675 GRAM | 1000.50   |
    And I am logged in as "Julien"
    And I am on the "foo" product page

  Scenario: Successfully view and edit localized number
    Given the field Nombre should contain "-12,50"
    When I change the "Nombre" to "-25,75"
    And I save the product
    Then the field Nombre should contain "-25,75"
    And the product "foo" should have the following values:
      | number | -25.75 |

  Scenario: Successfully view and edit localized metric
    Given the field Poids should contain "150,8675"
    When I change the "Poids" to "151"
    And I save the product
    Then the field Poids should contain "151"
    And the product "foo" should have the following values:
      | weight | 151.0000 GRAM |

  Scenario: Successfully view and edit localized price
    Given the field Prix should contain "1000,50"
    When I change the "Prix" to "1200,50 EUR"
    And I save the product
    Then the field Prix should contain "1200,50"
    And the product "foo" should have the following values:
      | price-EUR | 1200.50 |
