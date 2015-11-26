@javascript
Feature: Editing localized attribute values of a variant group also updates products
  In order to easily edit common attributes of variant group products
  As a product manager
  I need to be able to change attribute values of a variant group

  Background:
    Given a "footwear" catalog configuration
    And the following variant group values:
      | group             | attribute | value     |
      | caterpillar_boots | weight    | 10 GRAM   |
      | caterpillar_boots | rate_sale | 1900      |
      | caterpillar_boots | price     | 39.99 EUR |
    And the following products:
      | sku  | groups            | color | size |
      | boot | caterpillar_boots | black | 40   |
    And I am logged in as "Julien"
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Attributs" tab

  Scenario: Successfully change a pim_catalog_metric attribute of a variant group
    Given I change the "Weight" to "5,45"
    When I save the variant group
    Then the product "boot" should have the following values:
      | weight | 5.4500 GRAM |

  Scenario: Successfully change a pim_catalog_number attribute of a variant group
    Given I visit the "Marketing" group
    When I change the "Rate of sale" to "8000,2"
    And I save the variant group
    Then the product "boot" should have the following values:
      | rate_sale | 8000.2000 |

  Scenario: Successfully change a pim_catalog_price_collection attribute of a variant group
    Given I visit the "Marketing" group
    When I change the "€ Price" to "89,27"
    And I save the variant group
    Then the product "boot" should have the following values:
      | price | 89.27 EUR |

  Scenario: Fail to change a pim_catalog_metric attribute of a variant group
    Given I change the "Weight" to "5.45"
    When I save the variant group
    Then I should see validation error "Ce type de valeur attend , comme séparateur de décimales."

  Scenario: Fail to change a pim_catalog_number attribute of a variant group
    Given I visit the "Marketing" group
    When I change the "Rate of sale" to "8000.2"
    And I save the variant group
    Then I should see validation error "Ce type de valeur attend , comme séparateur de décimales."

  Scenario: Fail to change a pim_catalog_price_collection attribute of a variant group
    Given I visit the "Marketing" group
    When I change the "€ Price" to "89.27"
    And I save the variant group
    Then I should see validation error "Ce type de valeur attend , comme séparateur de décimales."
