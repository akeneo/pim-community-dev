@javascript
Feature: Define user rights
  In order to assign or remove some rights to a group of users
  As an administrator
  I need to be able to assign/remove rights

  Background:
    Given a "footwear" catalog configuration
    And a "boot" product

  Scenario: Successfully edit and apply user rights
    Given I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    When I revoke rights to resources List products and List channels
    And I save the role
    Then I should not see the text "There are unsaved changes."
    And I should be on the "Administrator" role page
    And I should not be able to access the products page
    And I should not be able to access the channels page
    But I should be able to access the attributes page

  Scenario Outline: Successfully hide entity creation, deletion buttons when user doesn't have the rights
    Given I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    When I revoke rights to resource <permission>
    And I save the role
    And I should not see the text "There are unsaved changes."
    And I am on the <page> page
    Then I should not see the text "<button>"

    Examples:
      | permission                 | page              | button                  |
      | Create an attribute        | attributes        | Create attribute        |

  Scenario Outline: Successfully hide entity creation and deletion buttons when user doesn't have the rights
    Given I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    When I revoke rights to resource <permission>
    And I save the role
    And I should not see the text "There are unsaved changes."
    And I am on the <page> page
    Then I should not see the secondary action "<button>"

    Examples:
      | permission                  | page                                         | button |
      | Remove a product            | "boot" product                               | Delete |
