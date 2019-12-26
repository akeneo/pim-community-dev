@javascript
Feature: Edit a product with localized attributes
  In order to enrich the catalog
  As a regular user
  I need to be able to view, edit and save a product with localizes values

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code   | label-fr_FR | type                         | scopable | unique | negative_allowed | decimals_allowed | group | metric_family | default_metric_unit |
      | number | Nombre      | pim_catalog_number           | 0        | 0      | 1                | 1                | other |               |                     |
      | weight | Poids       | pim_catalog_metric           | 0        | 0      | 1                | 1                | other | Weight        | GRAM                |
      | price  | Prix        | pim_catalog_price_collection | 0        | 0      |                  | 1                | other |               |                     |
      | date   | Date        | pim_catalog_date             | 0        | 0      |                  |                  | other |               |                     |
    And the following family:
      | code | label-en_US | attributes                   |
      | baz  | Baz         | sku,number,weight,price,date |
    And the following products:
      | sku | family | number | weight        | price-EUR | date       |
      | foo | baz    | -12.5  | 150.8675 GRAM | 1000.50   | 2015-05-28 |
    And I am logged in as "Julien"

  Scenario: Successfully view and edit localized number
    Given I am on the "foo" product page
    Then the field Nombre should contain "-12,50"
    When I change the "Nombre" to "-25,75"
    And I save the product
    Then the field Nombre should contain "-25,75"
    And the product "foo" should have the following values:
      | number | -25.75 |

  Scenario: Successfully view and edit localized metric
    Given I am on the "foo" product page
    Then the field Poids should contain "150,8675"
    When I change the "Poids" to "151"
    And I save the product
    Then the field Poids should contain "151"
    And the product "foo" should have the following values:
      | weight | 151.0000 GRAM |

  Scenario: Successfully view and edit localized price
    Given I am on the "foo" product page
    Then the field Prix should contain "1000,50"
    When I change the "Prix" to "1200,50 EUR"
    And I save the product
    Then the field Prix should contain "1200,50"
    And the product "foo" should have the following values:
      | price-EUR | 1200.50 |

  Scenario: Successfully view and edit localized date
    Given I am on the "foo" product page
    Then the field Date should contain "28/05/2015"
    When I change the "Date" to "01/12/2015"
    And I save the product
    Then the field Date should contain "01/12/2015"
    And the product "foo" should have the following values:
      | date | 2015-12-01 |

  Scenario: Switching locale should change the displayed format
    When I edit the "Julien" user
    And I visit the "Interfaces" tab
    And I fill in the following information:
      | Langue de l'interface | anglais (États-Unis) |
    And I save the user
    And I should see the text "System Navigation"
    And I am on the "foo" product page
    And I wait 3 seconds
    Then the field Date should contain "05/28/2015"
