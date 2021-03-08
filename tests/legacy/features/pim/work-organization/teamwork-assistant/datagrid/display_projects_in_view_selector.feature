@javascript
Feature: Display teamwork assistant projects in the datagrid view selector
  In order to display and select the projects I can work on
  As a contributor
  I need to be able to display the projects in the view selector

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
      | clothing         | Marketing           | own    |
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
      | attribute group | user group          | access | group |
      | marketing       | Marketing           | edit   | other |
      | marketing       | Technical Clothing  | view   | other |
      | marketing       | Technical High-Tech | view   | other |
      | marketing       | Read Only           | view   | other |
      | marketing       | Media manager       | view   | other |
      | technical       | Marketing           | view   | other |
      | technical       | Technical Clothing  | edit   | other |
      | technical       | Technical High-Tech | edit   | other |
      | technical       | Read Only           | view   | other |
      | technical       | Media manager       | none   | other |
      | other           | Marketing           | edit   | other |
      | other           | Technical Clothing  | edit   | other |
      | other           | Technical High-Tech | edit   | other |
      | other           | Read Only           | view   | other |
      | other           | Media manager       | view   | other |
      | media           | Marketing           | view   | other |
      | media           | Technical Clothing  | view   | other |
      | media           | Technical High-Tech | view   | other |
      | media           | Read Only           | view   | other |
      | media           | Media manager       | edit   | other |
    And the following families:
      | code     | label-en_US | attributes                                                     | requirements-ecommerce                     | requirements-mobile                |
      | tshirt   | TShirts     | sku,name,description,size,weight,release_date,material         | sku,name,size,description,material         | sku,name,size,description,material |
      | usb_keys | USB Keys    | sku,name,description,size,weight,release_date,capacity,picture | sku,name,size,description,capacity,picture | sku,name,size,description,capacity |
      | posters  | Posters     | sku,name,description,size,release_date,picture                 | sku,name,size,description,picture          | sku,name,size,description,picture  |
    And the following products:
      | sku                  | family   | categories         | name-en_US                | size-en_US | weight-en_US | weight-en_US-unit | release_date-en_US | release_date-fr_FR | material-en_US | capacity | capacity-unit |
      | tshirt-the-witcher-3 | tshirt   | clothing           | T-Shirt "The Witcher III" | M          | 5            | OUNCE             | 2015-06-20         | 2015-06-20         | cotton         |          |               |
      | tshirt-skyrim        | tshirt   | clothing           | T-Shirt "Skyrim"          | M          | 5            | OUNCE             |                    |                    |                |          |               |
      | tshirt-lcd           | tshirt   | clothing,high_tech | T-shirt LCD screen        | M          | 6            | OUNCE             | 2016-08-13         |                    |                |          |               |
      | usb-key-big          | usb_keys | high_tech          | USB Key Big 64Go          |            | 1            | OUNCE             | 2016-08-13         | 2016-10-13         |                |          |               |
      | usb-key-small        | usb_keys | high_tech          |                           |            | 1            | OUNCE             |                    |                    |                | 8        | GIGABYTE      |
      | poster-movie-contact | posters  | decoration         | Movie poster "Contact"    | A1         |              |                   |                    |                    |                |          |               |
    And I am logged in as "Julia"
    And I am on the "tshirt-the-witcher-3" product page
    And I visit the "All" group
    And I fill in the following information:
      | Description | A t-shirt with Geralt on it. |
    And I save the product
    When I am on the products grid
    And I open the category tree
    And I filter by "category" with operator "" and value "Clothing"
    And I close the category tree
    And I show the filter "weight"
    And I filter by "weight" with operator "<" and value "6 Ounce"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2118             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I am on the products grid
    And I switch view selector type to "Views"
    And I apply the "Default view" view
    Then I should be on the products page
    And I open the category tree
    And I filter by "category" with operator "" and value "Default"
    And I close the category tree
    And I type "capacity" in the manage filter input
    And I show the filter "capacity"
    And I filter by "capacity" with operator "=" and value "8 Gigabyte"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | Tech project       |
      | project-description | Technical project. |
      | project-due-date    | 12/13/2066         |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I logout

  Scenario: A contributor can display projects he can work on
    Given I am logged in as "Mary"
    When I am on the products grid
    And I switch view selector type to "Projects"
    Then I should be on the products page
    When I open the view selector
    Then I should see the "2016 summer collection" project

  Scenario: A contributor can display projects based on filter he doesn't have access to
    Given I am logged in as "Kathy"
    When I am on the products grid
    And I switch view selector type to "Projects"
    Then I should be on the products page
    When I open the view selector
    Then I should see the "Tech project" project

  Scenario: A contributor won't see a project he can't work on
    Given I am logged in as "Teddy"
    And I am on the products grid
    And I switch view selector type to "Projects"
    Then I should be on the products page
    When I open the view selector
    Then I should not see the "2016 summer collection" project
    But I should see the "Tech project" project

  Scenario: A contributor can search for a project name
    Given I am logged in as "Mary"
    And I am on the products grid
    And I switch view selector type to "Projects"
    And I open the view selector
    When I filter view selector with name "2016"
    Then I should see the "2016 summer collection" project
    When I filter view selector with name "bar"
    Then I should not see the "2016 summer collection" project

  Scenario: A contributor can see the completeness of a project in the list
    Given I am logged in as "Julia"
    When I am on the products grid
    And I switch view selector type to "Projects"
    And I open the view selector
    Then I should see the text "2016 summer collection"
    And I should see the text "50%"
