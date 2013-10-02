@javascript
Feature: Execute a job
  In order to launch an import
  As Julia
  I need to be able to execute a valid export

  Scenario: Fail to see the import button of a job with validation errors
    Given the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    Given I am logged in as "Julia"
    When I am on the "acme_product_import" import job page
    Then I should not see the "Import now" link

  Scenario: Successfully import a csv file of products
    Given the following file to import:
      """
      sku;family;categories;name;description
      SKU-001;Bag;leather,travel;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;Hat;travel;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;Hat;men;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      SKU-004;Hat;men;nec;justo sit amet nulla. Donec non justo. Proin non massa
      SKU-005;Bag;women,silk;non;tincidunt dui augue eu tellus. Phasellus elit pede, malesuada vel
      SKU-006;Bag;leather;ipsum;Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam auctor,
      SKU-007;Hat;;rutrum.;quis, pede. Praesent eu dui. Cum sociis natoque penatibus et
      SKU-008;Bag;coton;ligula;urna et arcu imperdiet ullamcorper. Duis at lacus. Quisque purus
      SKU-009;Hat;;porttitor;sagittis. Duis gravida. Praesent eu nulla at sem molestie sodales.
      SKU-010;Bag;men,silk;non,;vestibulum nec, euismod in, dolor. Fusce feugiat. Lorem ipsum dolor
      """
    And the following families:
      | code |
      | Bag  |
      | Hat  |
    And the following categories:
      | code    | label   | parent |
      | master  | Master  |        |
      | leather | Leather | master |
      | silk    | Silk    | master |
      | coton   | Coton   | master |
      | travel  | Travel  | master |
      | men     | Men     | master |
      | women   | Women   | master |
    And the following attributes:
      | code        | label       | type                   |
      | name        | Name        | pim_catalog_text       |
      | description | Description | pim_catalog_textarea   |
    And the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    And the following job "acme_product_import" configuration:
      | element   | property          | value                |
      | reader    | filePath          | {{ file to import }} |
      | reader    | uploadAllowed     | no                   |
      | reader    | delimiter         | ;                    |
      | reader    | enclosure         | "                    |
      | reader    | escape            | \                    |
      | processor | enabled           | yes                  |
      | processor | categories column | categories           |
      | processor | family column     | families             |
      | processor | channel           | ecommerce            |
    And I am logged in as "Julia"
    When I am on the "acme_product_import" import job page
    And I launch the import job
    Then there should be 10 products
    And the family of the product "SKU-006" should be "Bag"
    And product "SKU-007" should be enabled
    And the product "SKU-001" should have the following value:
      | name | Donec |
    And the product "SKU-002" should have the following value:
      | description | Pellentesque habitant morbi tristique senectus et netus et malesuada fames |

  Scenario: Fail to import a csv file of products with duplicate SKU
