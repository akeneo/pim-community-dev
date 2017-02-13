@javascript
Feature: Follow project completeness
  In order to follow my project progress
  As a project contributor
  I need to see my project completeness in a widget

  Background:
    Given the "activity_manager" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | marketing | Marketing   |
      | technical | Technical   |
      | other     | Other       |
      | media     | Media       |
    And the following attributes:
      | code         | label        | type       | localizable | scopable | decimals_allowed | metric_family | default_metric_unit | useable_as_grid_filter | group     | allowed extensions |
      | sku          | SKU          | identifier | no          | no       |                  |               |                     | yes                    | other     |                    |
      | name         | Name         | text       | yes         | no       |                  |               |                     | yes                    | marketing |                    |
      | description  | Description  | text       | yes         | yes      |                  |               |                     | no                     | marketing |                    |
      | size         | Size         | text       | yes         | no       |                  |               |                     | yes                    | marketing |                    |
      | weight       | Weight       | metric     | yes         | no       | no               | Weight        | GRAM                | yes                    | technical |                    |
      | release_date | Release date | date       | yes         | no       |                  |               |                     | yes                    | other     |                    |
      | capacity     | Capacity     | metric     | no          | no       | no               | Binary        | GIGABYTE            | yes                    | technical |                    |
      | material     | Material     | text       | yes         | no       |                  |               |                     | yes                    | technical |                    |
      | picture      | Picture      | image      | no          | yes      |                  |               |                     | no                     | media     | jpg                |
    And the following categories:
      | code       | label-en_US | parent  |
      | clothing   | Clothing    | default |
      | high_tech  | High-Tech   | default |
      | decoration | Decoration  | default |
    And the following product category accesses:
      | product category | user group          | access |
      | clothing         | Marketing           | edit   |
      | clothing         | Technical Clothing  | edit   |
      | clothing         | Technical High-Tech | none   |
      | clothing         | Read Only           | view   |
      | clothing         | Media manager       | edit   |
      | high_tech        | Marketing           | edit   |
      | high_tech        | Technical Clothing  | view   |
      | high_tech        | Technical High-Tech | edit   |
      | high_tech        | Read Only           | view   |
      | high_tech        | Media manager       | edit   |
      | decoration       | Marketing           | edit   |
      | decoration       | Technical Clothing  | none   |
      | decoration       | Technical High-Tech | none   |
      | decoration       | Read Only           | view   |
      | decoration       | Media manager       | edit   |
    And the following attribute group accesses:
      | attribute group | user group          | access |
      | marketing       | Marketing           | edit   |
      | marketing       | Technical Clothing  | view   |
      | marketing       | Technical High-Tech | view   |
      | marketing       | Read Only           | view   |
      | marketing       | Media manager       | view   |
      | technical       | Marketing           | view   |
      | technical       | Technical Clothing  | edit   |
      | technical       | Technical High-Tech | edit   |
      | technical       | Read Only           | view   |
      | technical       | Media manager       | none   |
      | other           | Marketing           | edit   |
      | other           | Technical Clothing  | edit   |
      | other           | Technical High-Tech | edit   |
      | other           | Read Only           | view   |
      | other           | Media manager       | view   |
      | media           | Marketing           | view   |
      | media           | Technical Clothing  | view   |
      | media           | Technical High-Tech | view   |
      | media           | Read Only           | view   |
      | media           | Media manager       | edit   |
    And the following families:
      | code     | label-en_US | attributes                                                   | requirements-ecommerce                 | requirements-mobile                    |
      | tshirt   | TShirts     | sku, name, description, size, weight, release_date, material | sku, name, size, description, material | sku, name, size, description, material |
      | usb_keys | USB Keys    | sku, name, description, size, weight, release_date, capacity | sku, name, size, description, capacity | sku, name, size, description, capacity |
      | posters  | Posters     | sku, name, description, size, release_date, picture          | sku, name, size, description, picture  | sku, name, size, description, picture  |
    And the following products:
      | sku                  | family   | categories         | name-en_US                | size-en_US | weight-en_US | weight-en_US-unit | release_date-en_US | release_date-fr_FR | material-en_US | capacity | capacity-unit |
      | tshirt-the-witcher-3 | tshirt   | clothing           | T-Shirt "The Witcher III" | M          | 5            | OUNCE             | 2015-06-20         | 2015-06-20         | cotton         |          |               |
      | tshirt-skyrim        | tshirt   | clothing           | T-Shirt "Skyrim"          | M          | 5            | OUNCE             |                    |                    |                |          |               |
      | tshirt-lcd           | tshirt   | clothing,high_tech | T-shirt LCD screen        | M          | 6            | OUNCE             | 2016-08-13         |                    |                |          |               |
      | usb-key-big          | usb_keys | high_tech          | USB Key Big 64Go          |            | 1            | OUNCE             | 2016-08-13         | 2016-10-13         |                |          |               |
      | usb-key-small        | usb_keys | high_tech          |                           |            | 1            | OUNCE             |                    |                    |                | 8        | GIGABYTE      |
      | poster-movie-contact | posters  | decoration         | Movie poster "Contact"    | A1         |              |                   |                    |                    |                |          |               |
    And the following projects:
      | label                  | owner | due_date   | description                                  | channel   | locale | product_filters                                                 |
      | Collection Summer 2030 | julia | 2030-10-25 | Please do your best to finish before Summer. | ecommerce | en_US  | []                                                              |
      | Collection Winter 2030 | julia | 2030-08-25 | Please do your best to finish before Winter. | ecommerce | en_US  | [{"field":"family.code", "operator":"IN", "value": ["tshirt"]}] |

  Scenario: Successfully display completeness on widget
    Given I am logged in as "Claude"
    And I am on the dashboard page
    Then I should see the Activity Manager widget
    And I should see the text "Collection Winter 2030 E-Commerce | English (United States)"
    And I should not see the contributor selector
    And I should see the following activity manager completeness:
      | todo | in_progress | done |
      |    0 |           2 |    1 |
    And I should see the text "0% PRODUCTS TO ENRICH"
    And I should see the text "67% PRODUCTS IN PROGRESS"
    And I should see the text "33% PRODUCTS DONE"
    And I should see the text "Please do your best to finish before Winter."
    And I should see the text "Due date: 08/25/2030"
    When I select "Collection Summer 2030" project
    Then I should see the text "Collection Summer 2030 E-Commerce | English (United States)"
    And I should not see the contributor selector
    And I should see the following activity manager completeness:
      | todo | in_progress | done |
      |    0 |           2 |    1 |
    And I should see the text "0% PRODUCTS TO ENRICH"
    And I should see the text "67% PRODUCTS IN PROGRESS"
    And I should see the text "33% PRODUCTS DONE"
    And I should see the text "Please do your best to finish before Summer."
    And I should see the text "Due date: 10/25/2030"

  Scenario: Successfully display the widget without project
    Given I am logged in as "admin"
    And I am on the dashboard page
    Then I should see the text "You have no current project, start a new project."
    And I should not see the project selector
    And I should not see the contributor selector
    When I follow "start a new project"
    Then I should be on the products page
