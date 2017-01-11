@javascript
Feature: Datagrid views
  In order to easily manage different views in the datagrid
  As a regular user
  I need to be able to create, update, apply and remove datagrid views

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku             | family   | name-en_US      |
      | purple-sneakers | sneakers | Purple sneakers |
      | black-sneakers  | sneakers | Black sneakers  |
      | black-boots     | boots    | Black boots     |
    And I am logged in as "Mary"

  Scenario: Successfully display the default view
    Given I am on the products page
    Then I should see the text "Default view"

  @ce
  Scenario: Successfully create a new view
    Given I am on the products page
    And I filter by "family" with operator "in list" and value "Sneakers"
    And I create the view:
      | new-view-label | Sneakers only |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    And I should see the text "Sneakers only"
    And I should see products purple-sneakers and black-sneakers
    But I should not see product black-boots

  Scenario: Successfully apply a view
    Given I am on the products page
    And I filter by "family" with operator "in list" and value "Boots"
    Then I should see product black-boots
    But I should not see products purple-sneakers and black-sneakers
    When I apply the "Default view" view
    Then I should be on the products page
    And I should see products black-boots, purple-sneakers and black-sneakers

  @ce
  Scenario: Successfully update a view
    Given I am on the products page
    And I filter by "family" with operator "in list" and value "Boots"
    And I create the view:
      | new-view-label | Some shoes |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    And I should see the text "Some shoes"
    And I should see product black-boots
    But I should not see products purple-sneakers and black-sneakers
    When I hide the filter "family"
    And I show the filter "family"
    And I filter by "family" with operator "in list" and value "Sneakers"
    And I update the view
    And I apply the "Some shoes" view
    Then I should be on the products page
    And I should see the text "Some shoes"
    And I should see products purple-sneakers and black-sneakers
    But I should not see product black-boots

  @ce
  Scenario: Successfully delete a view
    Given I am on the products page
    And I filter by "family" with operator "in list" and value "Boots"
    And I create the view:
      | new-view-label | Boots only |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    And I should see the text "Boots only"
    And I should see product black-boots
    But I should not see products purple-sneakers and black-sneakers
    When I delete the view "Boots only"
    And I confirm the deletion
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully removed"
    And I should see the text "Default view"
    But I should not see "Boots only"
    And I should see products black-boots, purple-sneakers and black-sneakers

  Scenario: Keep view per page
    Given I am on the products page
    When I change the page size to 25
    And I am on the attributes page
    And I change the page size to 50
    When I am on the products page
    Then the page size should be 25
    When I am on the attributes page
    Then the page size should be 50

  @ce
  Scenario: Successfully choose my default view
    Given I am on the products page
    And I filter by "family" with operator "in list" and value "Sneakers"
    And I create the view:
      | new-view-label | Sneakers only |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    When I am on the User profile show page
    And I press the "Edit" button
    Then I should see the text "Edit user - Mary Smith"
    When I visit the "Additional" tab
    Then I should see the text "Default product grid view"
    And I fill in the following information:
      | Default product grid view | Sneakers only |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I logout
    And I am logged in as "Julia"
    And I am on the products page
    Then I should see products black-boots, purple-sneakers and black-sneakers
    When I logout
    And I am logged in as "Mary"
    And I am on the products page
    Then I should see the text "Sneakers only"
    And I should see products purple-sneakers and black-sneakers
    But I should not see product black-boots
    When I press the "Reset" button
    Then I should see products black-boots, purple-sneakers and black-sneakers

  @ce
  Scenario: Successfully remove my default view
    Given I am on the products page
    And I filter by "family" with operator "in list" and value "Sneakers"
    And I create the view:
      | new-view-label | Sneakers only |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    When I am on the User profile show page
    And I press the "Edit" button
    Then I should see the text "Edit user - Mary Smith"
    When I visit the "Additional" tab
    Then I should see the text "Default product grid view"
    And I fill in the following information:
      | Default product grid view | Sneakers only |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the products page
    Then I should see the text "Sneakers only"
    When I delete the view
    And I confirm the deletion
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully removed"
    And I should see the text "Default view"
    But I should not see the text "Sneakers only"

  @ce
  Scenario: Successfully display values in grid when using a custom default view
    Given I am on the products page
    And I display the columns SKU, Name and Family
    Then I should see the text "purple-sneakers"
    When I create the view:
      | new-view-label | With name |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    When I am on the my account page
    And I press the "Edit" button
    And I visit the "Additional" tab
    Then I should see the text "Default product grid view"
    When I fill in the following information:
      | Default product grid view | With name |
    And I press the "Save" button
    And I am on the products page
    And I logout
    And I am logged in as "Mary"
    And I am on the products page
    And I filter by "category" with operator "unclassified" and value ""
    Then the row "purple-sneakers" should contain:
      | column | value           |
      | Name   | Purple sneakers |
      | Family | Sneakers        |
    And the row "black-sneakers" should contain:
      | column | value          |
      | Name   | Black sneakers |
      | Family | Sneakers       |
    And the row "black-boots" should contain:
      | column | value       |
      | Name   | Black boots |
      | Family | Boots       |

  @ce
  Scenario: Successfully change grid channel
    Given I am on the products page
    Then I should see the text "Tablet"
    When I filter by "scope" with operator "" and value "Mobile"
    And I create the view:
      | new-view-label | Mobile only |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    And I should see the text "Mobile only"
    And I should see the text "Mobile"

  @ce
  Scenario: Don't display view type switcher if there is only one view type
    Given I am on the products page
    Then I should not see the text "Views"
