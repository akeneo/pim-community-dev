@javascript
Feature: Datagrid views
  In order to easily manage different views in the datagrid
  As a regular user
  I need to be able to create, update, apply and remove datagrid views

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku             | family   | name-en_US      | manufacturer |
      | purple-sneakers | sneakers | Purple sneakers | Nike         |
      | black-sneakers  | sneakers | Black sneakers  |              |
      | black-boots     | boots    | Black boots     |              |
    And I am logged in as "Mary"

  Scenario: Successfully display the default view
    Given I am on the products grid
    Then I should see the text "Default view"
    Then I should see the columns ID, Image, Label, Family, Status, Complete, Created At, Updated At, Variant products

  Scenario: Successfully apply a view
    Given I am on the products grid
    And I filter by "family" with operator "in list" and value "Boots"
    Then I should see product black-boots
    But I should not see products purple-sneakers and black-sneakers
    When I apply the "Default view" view
    Then I should be on the products page
    And I should see products black-boots, purple-sneakers and black-sneakers

  Scenario: Successfully create and then update the filters of a view
    Given I am on the products grid
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

  Scenario: Successfully create and then update columns of a view
    Given I am on the products grid
    When I filter by "family" with operator "in list" and value "Sneakers"
    And I create the view:
      | new-view-label | Some shoes |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    And I should see the text "Some shoes"
    When I display the columns SKU, Name, Family and Manufacturer
    Then I should see the columns Family, Sku, Manufacturer, Name
    Then I should see the text "Nike"
    When I update the view
    And I apply the "Some shoes" view
    Then I should be on the products page
    And I should see the text "Some shoes"
    And I should see products purple-sneakers and black-sneakers
    And I should see the text "Nike"

  Scenario: Can not delete nor save a view that is not mine
    Given I am on the products grid
    And I filter by "family" with operator "in list" and value "Boots"
    And I create the view:
      | new-view-label | Boots only |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    And I should see the text "Boots only"
    And I should see product black-boots
    But I should not see products purple-sneakers and black-sneakers
    When I logout
    And I am logged in as "Julia"
    And I am on the products grid
    And I apply the "Boots only" view
    Then I should be on the products page
    And I should not be able to remove the view
    When I filter by "family" with operator "in list" and value "Sneakers"
    Then I should not be able to save the view

  Scenario: Successfully remove my default view
    Given I am on the products grid
    And I filter by "family" with operator "in list" and value "Sneakers"
    And I create the view:
      | new-view-label | Sneakers only |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    When I edit the "Mary" user
    When I visit the "Additional" tab
    Then I should see the text "Default product grid view"
    And I fill in the following information:
      | Default product grid view | Sneakers only |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I should see the text "Sneakers only"
    When I am on the products grid
    Then I should see the text "Sneakers only"
    When I delete the view
    And I confirm the deletion
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully removed"
    And I should see the text "Default view"
    And I should not see the text "Sneakers only"

  @critical
  Scenario: Successfully display values in grid when using a custom default view
    Given I am on the products grid
    And I display the columns SKU, Name, Family and Manufacturer
    Then I should see the text "Nike"
    When I create the view:
      | new-view-label | With name |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    When I edit the "Mary" user
    And I visit the "Additional" tab
    Then I should see the text "Default product grid view"
    When I fill in the following information:
      | Default product grid view | With name |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I logout
    And I am logged in as "Mary"
    And I am on the products grid
    And I open the category tree
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

  @critical
  Scenario: Successfully display the default view if my custom default view has been deleted
    Given I am on the products grid
    And I filter by "family" with operator "in list" and value "Boots"
    And I create the view:
      | new-view-label | Boots only |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    When I logout
    And I am logged in as "Julia"
    And I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see the text "Default product grid view"
    When I fill in the following information:
      | Default product grid view | Boots only |
    And I press the "Save" button
    And I am on the products grid
    Then I should see the text "Boots only"
    When I logout
    And I am logged in as "Mary"
    When I am on the products grid
    And I apply the "Boots only" view
    And I delete the view
    And I confirm the deletion
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully removed"
    And I should see the text "Default view"
    When I logout
    And I am logged in as "Julia"
    And I am on the products grid
    And I should see the text "Default view"
    But I should not see the text "Boots only"
