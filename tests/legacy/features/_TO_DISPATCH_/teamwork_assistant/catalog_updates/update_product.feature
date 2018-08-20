@javascript
Feature: Follow project completeness
  In order to follow my project progress
  As a project contributor
  I need to see my project completeness refreshed after a product update

  Background:
    Given the "teamwork_assistant" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | marketing | Marketing   |
      | technical | Technical   |
      | other     | Other       |
      | media     | Media       |
    And the following attributes:
      | code         | label-en_US  | label-fr_FR    | type                   | localizable | scopable | decimals_allowed | negative_allowed | metric_family | default_metric_unit | useable_as_grid_filter | group     | allowed_extensions |
      | sku          | SKU          | SKU            | pim_catalog_identifier | 0           | 0        |                  |                  |               |                     | 1                      | other     |                    |
      | name         | Name         | Nom            | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | marketing |                    |
      | description  | Description  | Description    | pim_catalog_text       | 1           | 1        |                  |                  |               |                     | 0                      | marketing |                    |
      | size         | Size         | Taille         | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | marketing |                    |
      | weight       | Weight       | Poid           | pim_catalog_metric     | 1           | 0        | 0                | 0                | Weight        | GRAM                | 1                      | technical |                    |
      | release_date | Release date | Date de sortie | pim_catalog_date       | 1           | 0        |                  |                  |               |                     | 1                      | other     |                    |
      | material     | Material     | matière        | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | technical |                    |
    And the following categories:
      | code       | label-en_US | parent  |
      | clothing   | Clothing    | default |
    And the following product category accesses:
      | product category | user group          | access |
      | clothing         | Marketing           | own    |
    And the following attribute group accesses:
      | attribute group | user group          | access |
      | marketing       | Marketing           | edit   |
      | other           | Marketing           | edit   |
      | technical       | Marketing           | edit   |
    And the following families:
      | code     | label-en_US | attributes                                             | requirements-ecommerce             | requirements-mobile                |
      | tshirt   | TShirts     | sku,name,description,size,weight,release_date,material | sku,name,size,description,material | sku,name,size,description,material |
    And the following products:
      | sku                  | family   | categories         | name-en_US                | size-en_US | weight-en_US | weight-en_US-unit | release_date-en_US | release_date-fr_FR | material-en_US |
      | tshirt-the-witcher-3 | tshirt   | clothing           | T-Shirt "The Witcher III" | M          | 5            | OUNCE             | 2015-06-20         | 2015-06-20         | cotton         |
      | tshirt-skyrim        | tshirt   | clothing           | T-Shirt "Skyrim"          | M          | 5            | OUNCE             |                    |                    |                |
      | tshirt-lcd           | tshirt   | clothing           | T-shirt LCD screen        | M          | 6            | OUNCE             | 2016-08-13         |                    |                |

  Scenario: Successfully refresh the project completeness after a product update
    Given I am logged in as "Julia"
    When I am on the products grid
    And I filter by "family" with operator "in list" and value "TShirts"
    And I should be on the products page
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | Collection Winter 2030                 |
      | project-description | My very awesome summer collection 2030 |
      | project-due-date    | 05/12/2030                             |
    And I press the "Save" button
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I am on the dashboard page
    Then I should see the teamwork assistant widget
    And I should see the text "Collection Winter 2030 E-Commerce | English (United States)"
    And I should see the following teamwork assistant completeness:
      | todo | in_progress | done |
      | 0    | 3           | 0    |
    When I am on the "tshirt-the-witcher-3" product page
    And I switch the locale to "fr_FR"
    And I visit the "All" group
    And I fill in the following information:
      | Description | Ma description |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I am on the dashboard page
    Then I should see the following teamwork assistant completeness:
      | todo | in_progress | done |
      | 0    | 3           | 0    |
    When I am on the "tshirt-the-witcher-3" product page
    And I switch the locale to "en_US"
    And I visit the "All" group
    And I fill in the following information:
      | Description | Description |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I am on the dashboard page
    Then I should see the following teamwork assistant completeness:
      | todo | in_progress | done |
      | 0    | 2           | 1    |
