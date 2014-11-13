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
    Then there should be 2 updates
    And I should see history:
      | version | property           | value |
      | 2       | weather_conditions | cold  |

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
    Then there should be 2 updates
    And I should see history:
      | version | property     | value |
      | 2       | manufacturer |       |

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
    Then there should be 3 updates
    And I should see history:
      | version | property           | value |
      | 2       | weather_conditions |       |
      | 3       | comment            |       |
