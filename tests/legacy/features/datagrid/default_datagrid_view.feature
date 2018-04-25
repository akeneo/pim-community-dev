@javascript
Feature: Published products datagrid views
  In order to easily manage the published products datagrid
  As a contributor
  I need to be able to select my default datagrid view

  # Override: EE default_datagrid_view.feature

  Background:
    Given a "footwear" catalog configuration
    And the following published products:
      | sku             | family   |
      | purple-sneakers | sneakers |
      | black-sneakers  | sneakers |
      | black-boots     | boots    |
    And I am logged in as "Mary"

  Scenario: A contributor can choose his default published products datagrid view from his profile
    Given I am on the published products grid
    And I filter by "family" with operator "in list" and value "Sneakers"
    And I create the view:
      | new-view-label | Sneakers only |
    Then I should be on the published index page
    And I should see the flash message "Datagrid view successfully created"
    When I am on the User profile show page
    And I press the "Edit" button
    Then I should see the text "Edit user - Mary Smith"
    When I visit the "Additional" tab
    Then I should see the text "Default published product grid view"
    When I fill in the following information:
      | Default published product grid view | Sneakers only |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I logout
    And I am logged in as "Julia"
    And I am on the published products grid
    Then I should see published products black-boots, purple-sneakers and black-sneakers
    When I logout
    And I am logged in as "Mary"
    And I am on the published products grid
    Then I should see the text "Sneakers only"
    And I should see published products purple-sneakers and black-sneakers
    But I should not see product black-boots
