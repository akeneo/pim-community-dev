@javascript
Feature: Datagrid views
  In order to easily manage different views in the datagrid
  As a regular user
  I need to be able to create, update, apply and remove datagrid views

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku             | family   |
      | purple-sneakers | sneakers |
      | black-sneakers  | sneakers |
      | black-boots     | boots    |
    And I am logged in as "Mary"
    And I am on the published page

  Scenario: Successfully choose my default published view
    Given I filter by "Family" with value "Sneakers"
    And I create the view:
      | label | Sneakers only |
    Then I should be on the published index page
    And I should see a flash message "Datagrid view successfully created"
    When I am on my profile page
    And I press the "Edit" button
    And I visit the "Additional" tab
    Then I should see the text "Default published product grid view"
    And I fill in the following information:
      | Default published product grid view | Sneakers only |
    And I press the "Save" button
    Then I logout
    And I am logged in as "Julia"
    And I am on the published index page
    Then I should see published black-boots, purple-sneakers and black-sneakers
    Then I logout
    And I am logged in as "Mary"
    And I am on the published index page
    Then I should see the text "Views Sneakers only"
    And I should see published purple-sneakers and black-sneakers
    But I should not see product black-boots
    Then I press the "Reset" button
    Then I should see published black-boots, purple-sneakers and black-sneakers
