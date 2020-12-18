@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product
  As a product manager
  I need to have access to a product history

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
      | version | property           | value      | date |
      | 2       | Weather conditions | snowy,cold | now  |
      | 2       | Name en            | Nice boots | now  |
    When I am on the attributes page
    And I search "Weather conditions"
    And I click on the "delete" action of the row which contains "Weather conditions"
    And I fill the input labelled 'Please type "weather_conditions"' with 'weather_conditions'
    And I press the "Delete" button
    And I am on the attributes page
    And I search "Name"
    And I click on the "delete" action of the row which contains "Name"
    And I fill the input labelled 'Please type "name"' with 'name'
    And I press the "Delete" button
    And I edit the "boots" product
    And the history of the product "boots" has been built
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property           | value      | date |
      | 2       | weather_conditions | snowy,cold | now  |
      | 2       | name-en_US         | Nice boots | now  |
