@javascript
Feature: Select a project to display products to enrich
  In order to easily display products I have to enrich in a project
  As a contributor
  I need to be able to select a project from many locations

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
    And I am logged in as "Julia"
    When I am on the products page
    And I filter by "category" with operator "" and value "clothing"
    And I show the filter "weight"
    And I filter by "weight" with operator "<" and value "6 Ounce"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I logout

  Scenario: A message is displayed if I have no projects to work on
    Given I am logged in as "Kathy"
    And I am on the products page
    And I switch view selector type to "Projects"
    Then I should see the text "Start a new project"
    When I filter by "category" with operator "" and value "clothing"
    Then the grid should contain 3 elements
    And I should see the text "Start a new project"

  Scenario: A contributor can select a project by selecting it in the datagrid view selector
    Given I am logged in as "Mary"
    And I am on the products page
    And I switch view selector type to "Projects"
    And I open the view selector
    When I apply the "2016 summer collection" project
    Then I should see products tshirt-skyrim and tshirt-the-witcher-3
    And I should see the text "2016 summer collection"

  Scenario: A contributor can select a project by clicking on the TODO section of the widget
    Given I am logged in as "Mary"
    And I am on the dashboard page
    When I click on the "todo" section of the activity manager widget
    Then I should be on the products page
    And I should see products tshirt-skyrim and tshirt-the-witcher-3
    And I should see the text "2016 summer collection"

  Scenario: A contributor can select a project by clicking on the IN PROGRESS section of the widget
    Given I am logged in as "Mary"
    And I am on the dashboard page
    When I click on the "in-progress" section of the activity manager widget
    Then I should be on the products page
    And I should see products tshirt-skyrim and tshirt-the-witcher-3
    And I should see the text "2016 summer collection"

  Scenario: A contributor can select a project from the project creation notification
    Given I am logged in as "Mary"
    And I am on the dashboard page
    When I click on the notification "Project calculation"
    Then I should be on the products page
    And I should see products tshirt-skyrim and tshirt-the-witcher-3
    And I should see the text "2016 summer collection"

  Scenario: A contributor must be alerted if he's leaving project scope by changing grid filters
    Given I am logged in as "Mary"
    And I am on the products page
    And I switch view selector type to "Projects"
    And I open the view selector
    When I apply the "2016 summer collection" project
    Then I should see products tshirt-skyrim and tshirt-the-witcher-3
    And I should see the text "2016 summer collection"
    When I filter by "category" with operator "" and value "high_tech"
    Then I should see the text "You're leaving project scope."
