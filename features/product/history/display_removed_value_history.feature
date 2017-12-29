@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product
  As a product manager
  I need to have access to a product history

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when a linked attribute option is removed
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | Family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I visit the "Product information" group
    And I change the "Weather conditions" to "Cold, Snowy"
    And I save the product
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property           | value      |
      | 2       | Weather conditions | snowy,cold |
    When I am on the "weather_conditions" attribute page
    And I visit the "Options" tab
    And I remove the "snowy" option
    And I confirm the deletion
    And I save the attribute
    Then I should not see the text "There are unsaved changes."
    When I am on the "boots" product page
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property           | value      |
      | 2       | Weather conditions | snowy,cold |

  @skip @info https://akeneo.atlassian.net/browse/TIP-233
  Scenario: Update product history when a linked category is removed
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU | boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I visit the "Categories" column tab
    And I visit the "2014 collection" tab
    And I expand the "2014_collection" category
    And I expand the "winter_collection" category
    And I click on the "winter_boots" category
    And I save the product
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property   | value        |
      | 2       | categories | winter_boots |
    When I edit the "winter_boots" category
    And I press the "Delete" button and wait for modal
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I visit the "History" column tab
    Then there should be 3 updates
    And I should see history:
      | version | property   | value |
      | 3       | categories |       |

  @skip @info https://akeneo.atlassian.net/browse/TIP-233
  Scenario: Update product history when multiple linked categories are removed
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU | boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I visit the "Categories" column tab
    And I visit the "2014 collection" tab
    And I expand the "2014_collection" category
    And I click on the "men_2014" category
    And I visit the "2015 collection" tab
    And I expand the "2015_collection" category
    And I expand the "men_2015" category
    And I click on the "men_2015_autumn" category
    And I click on the "men_2015_winter" category
    And I save the product
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property   | value                                    |
      | 2       | categories | men_2014,men_2015_autumn,men_2015_winter |
    When I edit the "men_2015_autumn" category
    And I press the "Delete" button and wait for modal
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I visit the "History" column tab
    Then there should be 3 updates
    And I should see history:
      | version | property   | value                    |
      | 3       | categories | men_2014,men_2015_winter |

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when a linked attribute is removed
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | Family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I visit the "Product information" group
    And I change the "Manufacturer" to "Converse"
    And I save the product
    When I edit the "boots" product
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property     | value    |
      | 2       | Manufacturer | Converse |
    When I am on the "manufacturer" attribute page
    And I press the secondary action "Delete"
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property     | value    |
      | 2       | manufacturer | Converse |

  @jira https://akeneo.atlassian.net/browse/PIM-3420
  Scenario: Update product history when multiple linked attributes are removed
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | Family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I visit the "Product information" group
    And I change the "Name" to "Nice boots"
    And I change the "Weather conditions" to "Cold, Snowy"
    And I save the product
    When I edit the "boots" product
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property           | value      |
      | 2       | Weather conditions | snowy,cold |
      | 2       | Name en            | Nice boots |
    When I am on the "weather_conditions" attribute page
    And I press the secondary action "Delete"
    And I confirm the deletion
    And I am on the "name" attribute page
    And I press the secondary action "Delete"
    And I confirm the deletion
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property           | value      |
      | 2       | weather_conditions | snowy,cold |
      | 2       | name-en_US         | Nice boots |
