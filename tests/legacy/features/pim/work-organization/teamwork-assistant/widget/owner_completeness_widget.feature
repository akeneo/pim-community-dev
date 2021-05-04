@javascript
Feature: Follow project completeness
  In order to follow the project progress
  As a project creator
  I need to see the completeness for each contributor in a widget

  Background:
    Given the "teamwork_assistant" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | marketing | Marketing   |
      | technical | Technical   |
      | other     | Other       |
      | media     | Media       |
    And the following attributes:
      | code         | label-en_US  | type                   | localizable | scopable | decimals_allowed | negative_allowed | metric_family | default_metric_unit | useable_as_grid_filter | group     | allowed_extensions |
      | sku          | SKU          | pim_catalog_identifier | 0           | 0        |                  |                  |               |                     | 1                      | other     |                    |
      | name         | Name         | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | marketing |                    |
      | description  | Description  | pim_catalog_text       | 1           | 1        |                  |                  |               |                     | 0                      | marketing |                    |
      | size         | Size         | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | marketing |                    |
      | weight       | Weight       | pim_catalog_metric     | 1           | 0        | 0                | 0                | Weight        | GRAM                | 1                      | technical |                    |
      | release_date | Release date | pim_catalog_date       | 1           | 0        |                  |                  |               |                     | 1                      | other     |                    |
      | capacity     | Capacity     | pim_catalog_metric     | 0           | 0        | 0                | 0                | Binary        | GIGABYTE            | 1                      | technical |                    |
      | material     | Material     | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | technical |                    |
      | picture      | Picture      | pim_catalog_image      | 0           | 1        |                  |                  |               |                     | 0                      | media     | jpg                |
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
      | code     | label-en_US | attributes                                             | requirements-ecommerce             | requirements-mobile                |
      | tshirt   | TShirts     | sku,name,description,size,weight,release_date,material | sku,name,size,description,material | sku,name,size,description,material |
      | usb_keys | USB Keys    | sku,name,description,size,weight,release_date,capacity | sku,name,size,description,capacity | sku,name,size,description,capacity |
      | posters  | Posters     | sku,name,description,size,release_date,picture         | sku,name,size,description,picture  | sku,name,size,description,picture  |
    And the following products:
      | sku                  | family   | categories         | name-en_US                | size-en_US | weight-en_US | weight-en_US-unit | release_date-en_US | release_date-fr_FR | material-en_US | capacity | capacity-unit |
      | tshirt-the-witcher-3 | tshirt   | clothing           | T-Shirt "The Witcher III" | M          | 5            | OUNCE             | 2015-06-20         | 2015-06-20         | cotton         |          |               |
      | tshirt-skyrim        | tshirt   | clothing           | T-Shirt "Skyrim"          | M          | 5            | OUNCE             |                    |                    |                |          |               |
      | tshirt-lcd           | tshirt   | clothing,high_tech | T-shirt LCD screen        | M          | 6            | OUNCE             | 2016-08-13         |                    |                |          |               |
      | usb-key-big          | usb_keys | high_tech          | USB Key Big 64Go          |            | 1            | OUNCE             | 2016-08-13         | 2016-10-13         |                |          |               |
      | usb-key-small        | usb_keys | high_tech          |                           |            | 1            | OUNCE             |                    |                    |                | 8        | GIGABYTE      |
      | poster-movie-contact | posters  | decoration         | Movie poster "Contact"    | A1         |              |                   |                    |                    |                |          |               |
    And the following projects:
      | label                                | owner | due_date   | description                                         | channel   | locale | product_filters                                                               |
      | Collection Summer 2030               | julia | 2030-10-28 | Please do your best to finish before Summer.        | ecommerce | en_US  | []                                                                            |
      | Collection Winter 2030               | julia | 2030-08-28 | Please do your best to finish before Winter.        | ecommerce | en_US  | [{"field":"family", "operator":"IN", "value": ["tshirt"]}]                    |
      | Collection SpaceSuits so trendy 2030 | julia | 2030-08-28 | Please we need to go on Mars before the end of 2030 | ecommerce | en_US  | [{"field":"sku", "operator":"=", "value": "product-that-does-not-exist-yet"}] |
    And I am logged in as "Julia"

  Scenario: Successfully see the project with the nearest due date first
    Given I am on the dashboard page
    And I should see the text "Collection Winter 2030"
    And I should see the text "Due date - 08/28/2030"

  Scenario: Successfully display completeness on widget
    Given I am on the dashboard page
    And I should see the text "Collection Winter 2030"
    And I should see the text "Contributors: All"
    And I should see the following teamwork assistant completeness:
      | todo | in_progress | done |
      | 0    | 3           | 0    |
    And I should see the text "0% products to start"
    And I should see the text "100% products in progress"
    And I should see the text "Please do your best to finish before Winter."
    And I should see the text "Due date - 08/28/2030"
    When I select "Collection Summer 2030" project
    Then I should see the text "Collection Summer 2030"
    And I should see the text "Contributors: All"
    And I should see the following teamwork assistant completeness:
      | todo | in_progress | done |
      | 0    | 6           | 0    |
    And I should see the text "0% products to start"
    And I should see the text "100% products in progress"
    And I should see the text "Please do your best to finish before Summer."
    And I should see the text "Due date - 10/28/2030"
    When I select "Claude Yachifeur" contributor
    Then I should see the text "Collection Summer 2030"
    And I should see the text "Claude Yachifeur"
    And I should see the following teamwork assistant completeness:
      | todo | in_progress | done |
      | 0    | 2           | 1    |
    And I should see the text "0% products to start"
    And I should see the text "67% products in progress"
    And I should see the text "33% products done"
    And I should see the text "Please do your best to finish before Summer."
    And I should see the text "Due date - 10/28/2030"

  Scenario: Successfully see the project when I am the creator even if it doesn't have any product yet
    Given I am on the dashboard page
    When I select "Collection SpaceSuits so trendy 2030" project
    Then I should see the text "Collection SpaceSuits so trendy 2030"
    And I should see the text "Due date - 08/28/2030"
