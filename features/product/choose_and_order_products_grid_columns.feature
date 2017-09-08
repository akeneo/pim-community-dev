@javascript
Feature: Choose and order product grids columns
  In order to works with data that I'm interested in the product datagrid
  As a regular user
  I need to be able to choose and order product grids columns

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku     |
      | sandals |
      | basket  |
    And I am logged in as "Mary"
    And I am on the products grid

  Scenario: Successfully display default columns
    Then I should see the columns Sku, Image, Label, Family, Status, Complete, Created At, Updated At, Groups

  @skip
  Scenario: Successfully hide some columns
    Given I hide the "Label" column
    Then I should see the columns Sku, Family, Status, Complete, Created At, Updated At, Groups

  @skip
  Scenario: Successfully order some columns
    Given I put the "Complete" column before the "Sku" one
    Then I should see the columns Sku, Family, Status, Complete, Created At, Updated At, Groups

  Scenario: Successfully hide removed attribute column that was previously selected to be displayed
    Given I display the columns SKU, Family and Name
    When I've removed the "name" attribute
    And I am on the products grid
    Then I should see the columns Sku and Family

  @jira https://akeneo.atlassian.net/browse/PIM-4861
  Scenario: Successfully display extra columns content like the name when filter on categories
    Given the following products:
      | sku     | name-en_US | categories        |
      | sandal1 | sandal one | summer_collection |
      | sandal2 | sandal two | summer_collection |
    And I display the columns SKU, Family and Name
    And I am on the products grid
    Then I should see the columns SKU, Family and Name
    And I open the category tree
    When I filter by "category" with operator "" and value "summer_collection"
    Then the grid should contain 2 elements
    And I should see entities sandal one, sandal two
