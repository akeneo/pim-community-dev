@javascript
Feature: Choose and order product grids columns
  In order to works with data that I'm interested in the product datagrid
  As a regular user
  I need to be able to choose and order product grids columns

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | family |
      | sandals | heels  |
      | basket  | heels  |
    And I am logged in as "Mary"
    And I am on the products page

  Scenario: Successfully display default columns
    Then I should see the columns Sku, Image, Label, Family, Status, Complete, Created At, Updated At, Groups, Variant products

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
    And I am on the products page
    Then I should see the columns Sku and Family

  @jira https://akeneo.atlassian.net/browse/PIM-4861
  Scenario: Successfully display extra columns content like the name when filter on categories
    Given the following products:
      | sku     | name-en_US | categories        |
      | sandal1 | sandal one | summer_collection |
      | sandal2 | sandal two | summer_collection |
    And I display the columns SKU, Family and Name
    And I am on the products page
    Then I should see the columns SKU, Family and Name
    And I should be able to use the following filters:
      | filter   | operator | value             | result                 |
      | category |          | summer_collection | sandal one, sandal two |
