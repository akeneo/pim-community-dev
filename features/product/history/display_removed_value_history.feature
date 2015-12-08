@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product
  As a product manager
  I need to have access to a product history

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
