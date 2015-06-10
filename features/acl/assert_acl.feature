@javascript
Feature: Define user rights
  In order to assign or remove some rights to a group of users
  As an administrator
  I need to be able to assign/remove rights

  Background:
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "Peter"

  @skip
  Scenario: Successfully edit and apply user rights
    Given I am on the "Administrator" role page
    When I remove rights to List products and List channels
    And I save the role
    Then I should be on the "Administrator" role page
    And I should not be able to access the products page
    And I should not be able to access the channels page
    But I should be able to access the attributes page
    And I reset the "Administrator" rights

  @skip
  Scenario: Successfully hide entity creation and deletion buttons when user doesn't have the rights
    Then removing the following permissions should hide the following buttons:
      | permission                  | page                                     | button                  | forbiddenPage            |
      | Create an association type  | association types                        | Create association type | AssociationType creation |
      | Create a channel            | channels                                 | Create channel          | channel creation         |
      | Create a family             | families                                 | Create family           | Family creation          |
      | Create a group              | product groups                           | Create group            | ProductGroup creation    |
      | Create a variant group      | variant groups                           | Create variant group    | VariantGroup creation    |
      | Create a group type         | group types                              | Create group type       | GroupType creation       |
      | Create a product            | products                                 | Create product          | Product creation         |
      | Create an attribute         | attributes                               | Create attribute        | Attribute creation       |
      | Create an export profile    | exports                                  | Create export profile   | Export creation          |
      | Create an import profile    | imports                                  | Create import profile   | Import creation          |
      | Remove an association type  | "X_SELL" association type                | Delete                  |                          |
      | Remove an attribute group   | "Sizes" attribute group                  | Delete                  |                          |
      | Remove a category           | "sandals" category                       | Delete                  |                          |
      | Remove a channel            | "mobile" channel                         | Delete                  |                          |
      | Remove a family             | "boots" family                           | Delete                  |                          |
      | Remove a group              | "similar_boots" product group            | Delete                  |                          |
      | Remove a variant group      | "caterpillar_boots" variant group        | Delete                  |                          |
      | Remove a group type         | "RELATED" group type                     | Delete                  |                          |
      | Remove a product            | "boot" product                           | Delete                  |                          |
      | Download the product as PDF | "boot" product                           | Pdf                     |                          |
      | Remove an attribute         | "color" attribute                        | Delete                  |                          |
      | Remove an export profile    | "footwear_option_export" export job edit | Delete                  |                          |
      | Remove an import profile    | "footwear_group_import" import job edit  | Delete                  |                          |

  @jira https://akeneo.atlassian.net/browse/PIM-3758
  Scenario: Successfully remove and add the List categories right
    Given I am on the "Administrator" role page
    When I remove rights to List categories
    And I save the role
    And I wait 10 seconds
    Then I am on the products page
    And I should not see "2014 Collection"
    Then I reset the "Administrator" rights
    And I wait 10 seconds
    And I am on the products page
    And I should see "2014 Collection"
