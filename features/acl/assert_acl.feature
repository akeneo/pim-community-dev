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
    Then I should be on the "Administrator" role page
    And I should not be able to access the products page
    And I should not be able to access the channels page
    But I should be able to access the attributes page

  Scenario Outline: Test page access when user doesn't have the rights
    Given I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    When I revoke rights to resource <permission>
    And I save the role
    And I am on the <page> page
    Then I should not be able to access the <forbiddenPage> page

    Examples:
      | permission                 | page              | forbiddenPage            |
      | Create an association type | association types | AssociationType creation |
      | Create a channel           | channels          | channel creation         |
      | Create a group             | product groups    | ProductGroup creation    |
      | Create a variant group     | variant groups    | VariantGroup creation    |
      | Create a group type        | group types       | GroupType creation       |
      | Create an attribute        | attributes        | Attribute creation       |

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
      | Create an association type | association types | Create association type |
      | Create a channel           | channels          | Create channel          |
      | Create a family            | families          | Create family           |
      | Create a group             | product groups    | Create group            |
      | Create a variant group     | variant groups    | Create variant group    |
      | Create a group type        | group types       | Create group type       |
      | Create an attribute        | attributes        | Create attribute        |

  Scenario Outline: Successfully hide entity creation and deletion buttons when user doesn't have the rights
    Given I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    When I revoke rights to resource <permission>
    And I save the role
    And I am on the <page> page
    Then I should not see the text "<button>"

    Examples:
      | permission                  | page                                         | button |
      | Remove a channel            | "mobile" channel                             | Delete |
      | Remove an association type  | "X_SELL" association type                    | Delete |
      | Remove an attribute group   | "Sizes" attribute group                      | Delete |
      | Remove a category           | "sandals" category                           | Delete |
      | Remove a family             | "boots" family                               | Delete |
      | Remove a group              | "similar_boots" product group                | Delete |
      | Remove a variant group      | "caterpillar_boots" variant group            | Delete |
      | Remove a group type         | "RELATED" group type                         | Delete |
      | Remove a product            | "boot" product                               | Delete |
      | Remove an attribute         | "color" attribute                            | Delete |
      | Remove an export profile    | "csv_footwear_option_export" export job edit | Delete |
      | Remove an import profile    | "csv_footwear_group_import" import job edit  | Delete |

  @jira https://akeneo.atlassian.net/browse/PIM-3758
  Scenario: Successfully remove and add the List categories rights
    Given I am logged in as "Peter"
    When I am on the products grid
    And I open the category tree
    Then I should see the text "2014 Collection"
    When  I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource List categories
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the products grid
    Then I should not see the text "2014 Collection"
