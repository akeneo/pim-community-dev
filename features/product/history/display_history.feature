@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product
  As a product manager
  I need to have access to a product history

  Scenario: Display product updates
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | sandals-001 |
    And I press the "Save" button in the popin
    And I edit the "sandals-001" product
    And the history of the product "sandals-001" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property | value       |
      | 1       | SKU      | sandals-001 |

  @jira https://akeneo.atlassian.net/browse/PIM-3628
  Scenario: Update product history when updating product prices
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | price          |
      | boots | 10 EUR, 20 USD |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property  | value |
      | 1       | Price EUR | 10    |
      | 1       | Price USD | 20    |
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    And I change the "Price" to "19 USD"
    And I save the product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property  | value |
      | 2       | Price USD | 19    |
    When I close the "history" panel
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    And I remove the "Price" attribute
    And I confirm the deletion
    And I save the product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 3 updates
    And I should see history:
      | version | property  | value |
      | 3       | Price EUR |       |
      | 3       | Price USD |       |

  @jira https://akeneo.atlassian.net/browse/PIM-3628
  Scenario: Update product history when updating product metric
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | length        |
      | boots | 30 CENTIMETER |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property    | value      |
      | 1       | Length      | 30         |
      | 1       | Length unit | Centimeter |
    When I close the "history" panel
    When I visit the "Attributes" tab
    And I change the "Length" to "35 CENTIMETER"
    And I save the product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property | value   |
      | 2       | Length   | 35      |
    When I close the "history" panel
    When I visit the "Attributes" tab
    And I remove the "Length" attribute
    And I confirm the deletion
    And I save the product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 3 updates
    And I should see history:
      | version | property    | value |
      | 3       | Length      |       |
      | 3       | Length unit |       |

  @jira https://akeneo.atlassian.net/browse/PIM-3628
  Scenario: Update product history when updating product media
    Given a "footwear" catalog configuration
    And a "boots" product
    And I am logged in as "Julia"
    When I edit the "boots" product
    And I add available attribute Side view
    And I visit the "Media" group
    And I attach file "SNKRS-1R.png" to "Side view"
    And I save the product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property  | value           |
      | 2       | Side view | .*SNKRS_1R\.png |
    When I close the "history" panel
    When I visit the "Attributes" tab
    And I visit the "Media" group
    And I remove the "Side view" file
    And I save the product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 3 updates
    And I should see history:
      | version | property  | value |
      | 3       | Side view |       |
