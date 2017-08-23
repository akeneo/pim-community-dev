@javascript
Feature: Update the product history
  In order to know by who, when and what changes have been made to a product
  As a product manager
  I need to have access to a product history

  Background:
    Given a "apparel" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | tshirt   |
      | family | T-shirts |
    And I press the "Save" button in the popin
    And I wait to be on the "tshirt" product page

  @jira https://akeneo.atlassian.net/browse/PIM-3628
  Scenario: Update product history when updating product prices
    Given I visit the "Sales" group
    And I change the Price to "20 USD"
    And I change the Price to "10 EUR"
    And I save the product
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property  | value  |
      | 2       | Price EUR | €10.00 |
      | 2       | Price USD | $20.00 |
    When I visit the "Attributes" column tab
    And I change the "Price" to "19 USD"
    And I save the product
    And I visit the "History" column tab
    Then there should be 3 updates
    And I should see history:
      | version | property  | value  |
      | 3       | Price USD | $19.00 |

  @jira https://akeneo.atlassian.net/browse/PIM-3628
  Scenario: Update product history when updating product metric
    Given I visit the "Additional information" group
    And I change the "Washing temperature" to "40 Celsius"
    And I save the product
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property                 | value   |
      | 2       | Washing temperature      | 40      |
      | 2       | Washing temperature unit | Celsius |
    When I visit the "Attributes" column tab
    And I change the "Washing temperature" to "35 Celsius"
    And I save the product
    When I visit the "History" column tab
    Then there should be 3 updates
    And I should see history:
      | version | property            | value |
      | 3       | Washing temperature | 35    |

  @jira https://akeneo.atlassian.net/browse/PIM-3628
  Scenario: Update product history when updating product media
    Given I visit the "Media" group
    And I attach file "SNKRS-1R.png" to "Thumbnail"
    And I save the product
    When I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property  | value           |
      | 2       | Thumbnail | .*SNKRS_1R\.png |
