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

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when a linked attribute option is removed
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | weather_conditions |
      | boots | cold,snowy         |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property           | value      |
      | 1       | Weather conditions | cold,snowy |
    When I close the "history" panel
    When I edit the "weather_conditions" attribute
    And I visit the "Values" tab
    And I remove the "snowy" option
    And I confirm the deletion
    And I save the attribute
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 updates
    And I should see history:
      | version | property           | value      |
      | 1       | Weather conditions | cold,snowy |

  Scenario: Update product history when a linked category is removed
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | categories   |
      | boots | winter_boots |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property   | value        |
      | 1       | categories | winter_boots |
    When I close the "history" panel
    When I edit the "winter_boots" category
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property   | value |
      | 2       | categories |       |

  Scenario: Update product history when multiple linked categories are removed
    Given an "apparel" catalog configuration
    And the following product:
      | sku   | categories                               |
      | boots | men_2014,men_2015_autumn,men_2015_winter |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property   | value                                    |
      | 1       | categories | men_2014,men_2015_autumn,men_2015_winter |
    When I close the "history" panel
    When I edit the "men_2015_autumn" category
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property   | value                    |
      | 2       | categories | men_2014,men_2015_winter |

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when a linked attribute is removed
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | manufacturer |
      | boots | Converse     |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property     | value    |
      | 1       | Manufacturer | Converse |
    When I close the "history" panel
    When I edit the "manufacturer" attribute
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 updates
    And I should see history:
      | version | property     | value    |
      | 1       | manufacturer | Converse |

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when multiple linked attributes are removed
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | weather_conditions | comment    |
      | boots | cold,snowy         | nice boots |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property           | value      |
      | 1       | Weather conditions | cold,snowy |
      | 1       | Comment            | nice boots |
    When I close the "history" panel
    When I edit the "weather_conditions" attribute
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "comment" attribute
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property           | value      |
      | 1       | weather_conditions | cold,snowy |
      | 1       | comment            | nice boots |

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
      | 1       | Price EUR | 10.00 |
      | 1       | Price USD | 20.00 |
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    And I change the "Price" to "19 USD"
    And I save the product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property  | value |
      | 2       | Price USD | 19.00 |
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
      | 1       | Length unit | CENTIMETER |
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
