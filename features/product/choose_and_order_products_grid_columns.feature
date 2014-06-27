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
    And I am on the products page

  Scenario: Succesfully display default columns
    Then I should see the columns Sku, Label, Family, Status, Complete, Created At, Updated At, Groups

  @skip
  Scenario: Succesfully hide some columns
    Given I hide the "Label" column
    Then I should see the columns Sku, Family, Status, Complete, Created At, Updated At, Groups

  @skip
  Scenario: Succesfully order some columns
    Given I put the "Complete" column before the "Sku" one
    Then I should see the columns Sku, Family, Status, Complete, Created At, Updated At, Groups

  Scenario: Succesfully hide removed attribute column that was previously selected to be displayed
    Given I display the columns sku, family and name
    When I've removed the "name" attribute
    And I am on the products page
    Then I should see the columns Sku and Family
