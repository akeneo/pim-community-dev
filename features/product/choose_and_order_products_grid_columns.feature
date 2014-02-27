@javascript
Feature: Choose and order product grids columns
  In order to works with data that I'm interested in the product datagrid
  As Julia
  I need to be able to choose and order product grids columns

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku     |
      | sandals |
      | basket  |
    And I am logged in as "Julia"

  Scenario: Succesfully display all columns by default
    Given I am on the products page
    Then I should see the columns Sku, Label, Family, Status, Complete, Created At, Updated At, Groups, Color, Name, Price, Rating and Size

  @skip
  Scenario: Succesfully hide some columns
    Given I am on the products page
    When I hide the "Color" column
    Then I should see the columns Sku, Label, Family, Status, Complete, Created At, Updated At, Groups, Name, Price, Rating and Size

  @skip
  Scenario: Succesfully order some columns
    Given I am on the products page
    When I put the "Color" column before the "Complete" one
    Then I should see the columns Sku, Label, Family, Status, Color, Complete, Created At, Updated At, Groups, Name, Price, Rating and Size

  Scenario: Succesfully hide removed attribute column that was previously selected to be displayed
    Given I've displayed the columns sku, family and name
    And I've removed the "name" attribute
    And I am on the products page
    Then I should see the columns Sku and Family
