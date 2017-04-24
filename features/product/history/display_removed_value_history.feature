@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product
  As a product manager
  I need to have access to a product history

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when a linked attribute option is removed
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I add available attributes Weather conditions
    And I change the "Weather conditions" to "Cold, Snowy"
    And I save the product
    When I open the history
    Then there should be 2 update
    And I should see history:
      | version | property           | value      |
      | 2       | Weather conditions | cold,snowy |
    When I close the "history" panel
    When I edit the "weather_conditions" attribute
    And I visit the "Values" tab
    And I remove the "snowy" option
    And I confirm the deletion
    And I save the attribute
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property           | value      |
      | 2       | Weather conditions | cold,snowy |

  Scenario: Update product history when a linked category is removed
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I visit the "Categories" column tab
    And I select the "2014 collection" tree
    And I expand the "2014_collection" category
    And I expand the "winter_collection" category
    And I click on the "winter_boots" category
    And I save the product
    When I open the history
    Then there should be 2 update
    And I should see history:
      | version | property   | value        |
      | 2       | categories | winter_boots |
    When I close the "history" panel
    When I edit the "winter_boots" category
    And I press the "Delete" button and wait for modal
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 3 updates
    And I should see history:
      | version | property   | value |
      | 3       | categories |       |

  Scenario: Update product history when multiple linked categories are removed
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I visit the "Categories" column tab
    And I select the "2014 collection" tree
    And I expand the "2014_collection" category
    And I click on the "men_2014" category
    And I select the "2015 collection" tree
    And I expand the "2015_collection" category
    And I expand the "men_2015" category
    And I click on the "men_2015_autumn" category
    And I click on the "men_2015_winter" category
    And I save the product
    When I open the history
    Then there should be 2 update
    And I should see history:
      | version | property   | value                                    |
      | 2       | categories | men_2014,men_2015_autumn,men_2015_winter |
    When I close the "history" panel
    When I edit the "men_2015_autumn" category
    And I press the "Delete" button and wait for modal
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 3 updates
    And I should see history:
      | version | property   | value                    |
      | 3       | categories | men_2014,men_2015_winter |

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when a linked attribute is removed
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I add available attributes Manufacturer
    And I change the "Manufacturer" to "Converse"
    And I save the product
    When I edit the "boots" product
    When I open the history
    Then there should be 2 update
    And I should see history:
      | version | property     | value    |
      | 2       | Manufacturer | Converse |
    When I close the "history" panel
    When I edit the "manufacturer" attribute
    And I press the "Delete" button and wait for modal
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property     | value    |
      | 2       | manufacturer | Converse |

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when multiple linked attributes are removed
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I add available attributes Weather conditions, Comment
    And I change the "Weather conditions" to "Cold, Snowy"
    And I visit the "Other" group
    And I change the "Comment" to "nice boots"
    And I save the product
    When I edit the "boots" product
    When I open the history
    Then there should be 2 update
    And I should see history:
      | version | property           | value      |
      | 2       | Weather conditions | cold,snowy |
      | 2       | Comment            | nice boots |
    When I close the "history" panel
    When I edit the "weather_conditions" attribute
    And I press the "Delete" button and wait for modal
    And I confirm the deletion
    And I edit the "comment" attribute
    And I press the "Delete" button and wait for modal
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I open the history
    Then there should be 2 update
    And I should see history:
      | version | property           | value      |
      | 2       | weather_conditions | cold,snowy |
      | 2       | comment            | nice boots |
