@javascript
Feature: Catalog updates - Remove a channel used by a project
  In order to remove a channel
  As a user
  I need to delete all projects impacted by this one

  Background:
    Given the "teamwork_assistant" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | marketing | Marketing   |
      | technical | Technical   |
      | other     | Other       |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | marketing       | Marketing  | edit   |
      | technical       | Marketing  | edit   |
      | other           | Marketing  | edit   |
    And the following attributes:
      | code        | label-en_US | type                   | localizable | scopable | decimals_allowed | metric_family | default_metric_unit | useable_as_grid_filter | group     | allowed_extensions |
      | sku         | SKU         | pim_catalog_identifier | 0           | 0        |                  |               |                     | 1                      | other     |                    |
      | name        | Name        | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
      | description | Description | pim_catalog_text       | 1           | 1        |                  |               |                     | 0                      | marketing |                    |
      | size        | Size        | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
    And the following categories:
      | code     | label-en_US | parent  |
      | clothing | Clothing    | default |
    And the following families:
      | code   | label-en_US | attributes                | requirements-ecommerce    | requirements-mobile       |
      | tshirt | TShirts     | sku,name,description,size | sku,name,size,description | sku,name,size,description |
    And the following products:
      | sku                  | family | categories | name-en_US                | size-en_US |
      | tshirt-the-witcher-3 | tshirt | clothing   | T-Shirt "The Witcher III" | M          |
      | tshirt-skyrim        | tshirt | clothing   | T-Shirt "Skyrim"          | M          |
      | tshirt-lcd           | tshirt | clothing   | T-shirt LCD screen        | M          |
    And the following projects:
      | label                  | owner | due_date   | description                                  | channel   | locale | product_filters                                            |
      | Collection Summer 2030 | julia | 2030-10-28 | Please do your best to finish before Summer. | ecommerce | en_US  | [{"field":"family", "operator":"IN", "value": ["tshirt"]}] |
      | Collection Winter 2030 | julia | 2030-08-28 | Please do your best to finish before Winter. | mobile    | en_US  | [{"field":"family", "operator":"IN", "value": ["tshirt"]}] |
    And I am logged in as "Julia"

  Scenario: Remove a channel used by a project from the grid
    Given I am on the channels page
    When I click on the "Delete" action of the row which contains "E-Commerce"
    And I confirm the deletion
    Then I should see the flash message "Channel successfully removed"
    And I should not see channel E-Commerce
    When I am on the dashboard page
    Then I should not see the "Collection Summer 2030" project in the widget
    But I should see the "Collection Winter 2030" project in the widget
    When I am on the products grid
    And I switch view selector type to "Projects"
    Then I should not see the "Collection Summer 2030" project
    But I should see the "Collection Winter 2030" project

  Scenario: Remove a channel used by a project from channel page
    Given I am on the "mobile" channel page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Channel successfully removed"
    And I should not see channel mobile
    When I am on the dashboard page
    Then I should not see the "Collection Winter 2030" project in the widget
    But I should see the "Collection Summer 2030" project in the widget
    When I am on the products grid
    And I switch view selector type to "Projects"
    Then I should not see the "Collection Winter 2030" project
    But I should see the "Collection Summer 2030" project
