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
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value       |
      | 1       | sku      | sandals-001 |

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when a linked attribute option is removed
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | weather_conditions |
      | boots | cold,snowy         |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property           | value      |
      | 1       | weather_conditions | cold,snowy |
    When I edit the "weather_conditions" attribute
    And I visit the "Values" tab
    And I remove the "snowy" option
    And I save the attribute
    And I edit the "boots" product
    And I visit the "History" tab
    Then there should be 1 updates
    And I should see history:
      | version | property           | value      |
      | 1       | weather_conditions | cold,snowy |

  Scenario: Update product history when a linked category is removed
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | categories   |
      | boots | winter_boots |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property   | value        |
      | 1       | categories | winter_boots |
    When I edit the "winter_boots" category
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "boots" product
    And I visit the "History" tab
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
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property   | value                                    |
      | 1       | categories | men_2014,men_2015_autumn,men_2015_winter |
    When I edit the "men_2015" category
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "boots" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property   | value    |
      | 2       | categories | men_2014 |

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when a linked attribute is removed
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | manufacturer |
      | boots | Converse     |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property     | value    |
      | 1       | manufacturer | Converse |
    When I edit the "manufacturer" attribute
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "boots" product
    And I visit the "History" tab
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
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property           | value      |
      | 1       | weather_conditions | cold,snowy |
      | 1       | comment            | nice boots |
    When I edit the "weather_conditions" attribute
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "comment" attribute
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "boots" product
    And I visit the "History" tab
    Then there should be 1 updates
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
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property  | value |
      | 1       | price-EUR | 10    |
      | 1       | price-USD | 20    |
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    And I change the "$ Price" to "19"
    And I save the product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property  | value |
      | 2       | price-USD | 19    |
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    And I remove the "Price" attribute
    And I visit the "History" tab
    Then there should be 3 updates
    And I should see history:
      | version | property  | value |
      | 3       | price-EUR |       |
      | 3       | price-USD |       |

  @jira https://akeneo.atlassian.net/browse/PIM-3628
  Scenario: Update product history when updating product metric
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | length        |
      | boots | 30 CENTIMETER |
    And I am logged in as "Julia"
    When I edit the "boots" product
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property    | value      |
      | 1       | length      | 30         |
      | 1       | length-unit | CENTIMETER |
    When I visit the "Attributes" tab
    And I change the "Length" to "35"
    And I save the product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value   |
      | 2       | length   | 35      |
    When I visit the "Attributes" tab
    And I remove the "Length" attribute
    And I visit the "History" tab
    Then there should be 3 updates
    And I should see history:
      | version | property    | value |
      | 3       | length      |       |
      | 3       | length-unit |       |

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
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property  | value            |
      | 2       | side_view | (.)*SNKRS-1R.png |
    When I visit the "Attributes" tab
    And I visit the "Media" group
    And I remove the "Side view" file
    And I save the product
    And I visit the "History" tab
    Then there should be 3 updates
    And I should see history:
      | version | property  | value |
      | 3       | side_view |       |
