@javascript
Feature: Define user rights
  In order to assign or remove some rights to a group of users
  As an admin
  I need to be able to assign/remove rights

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully edit and apply user rights
    Given I am on the "Administrator" role page
    When I remove rights to List products and List channels
    And I save the role
    Then I should be on the "Administrator" role page
    And I should see "List products None"
    And I should see "List channels None"
    And I should not be able to access the products page
    And I should not be able to access the channels page
    But I should be able to access the attributes page
    And I reset the "Administrator" rights

  Scenario Outline: Successfully hide entity creation buttons when user doesn't have the rights
    Given I am on the "Administrator" role page
    And I remove rights to <permission>
    And I save the role
    When I am on the <page> page
    Then I should not see "<button>"
    And I reset the "Administrator" rights

    Examples:
      | permission               | page           | button                |
      | Create an association    | associations   | Create association    |
      | Create a channel         | channels       | Create channel        |
      | Create a family          | families       | Create family         |
      | Create a group           | product groups | Create group          |
      | Create a group           | variant groups | Create variant group  |
      | Create a group type      | group types    | Create group type     |
      | Create a product         | products       | Create product        |
      | Create product attribute | attributes     | Create attribute      |
      | Create an export profile | exports        | Create export profile |
      | Create an import profile | imports        | Create import profile |
