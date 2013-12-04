@javascript
Feature: Execute a job
  In order to use existing product information
  As Julia
  I need to be able to import products

  Background:
    Given the "default" catalog configuration
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
    And the following product groups:
      | code  | label     | attributes | type   |
      | CROSS | Bag Cross |            | X_SELL |
    And the following attributes:
      | code        | label       | type     |
      | name        | Name        | text     |
      | description | Description | textarea |
      | prices      | Prices      | prices   |
    And the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    And the following job "acme_product_import" configuration:
      | filePath          |            |
      | uploadAllowed     | no         |
      | delimiter         | ;          |
      | enclosure         | "          |
      | escape            | \          |
      | enabled           | yes        |
      | categories column | categories |
      | family column     | families   |
      | groups column     | groups     |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of products
    Given the following file to import:
      """
      sku;family;groups;categories;name;description
      SKU-001;Bag;CROSS;leather,travel;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;Hat;;travel;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;Hat;;men;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      SKU-004;Hat;;men;nec;justo sit amet nulla. Donec non justo. Proin non massa
      SKU-005;Bag;CROSS;women,silk;non;tincidunt dui augue eu tellus. Phasellus elit pede, malesuada vel
      SKU-006;Bag;CROSS;leather;ipsum;Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam auctor,
      SKU-007;Hat;;;rutrum.;quis, pede. Praesent eu dui. Cum sociis natoque penatibus et
      SKU-008;Bag;CROSS;coton;ligula;urna et arcu imperdiet ullamcorper. Duis at lacus. Quisque purus
      SKU-009;Hat;;;porttitor;sagittis. Duis gravida. Praesent eu nulla at sem molestie sodales.
      SKU-010;Bag;CROSS;men,silk;non,;vestibulum nec, euismod in, dolor. Fusce feugiat. Lorem ipsum dolor
      """
    And the following job "acme_product_import" configuration:
      | filePath | {{ file to import }} |
    When I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be 10 products
    And the family of the product "SKU-006" should be "Bag"
    And product "SKU-007" should be enabled
    And the product "SKU-001" should have the following value:
      | name | Donec |
    And the product "SKU-002" should have the following value:
      | description | Pellentesque habitant morbi tristique senectus et netus et malesuada fames |

  Scenario: Successfully ignore duplicate unique data
    Given the following file to import:
      """
      sku;family;groups;categories;name;description
      SKU-001;Bag;;leather,travel;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-001;Hat;;travel;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      """
    And the following job "acme_product_import" configuration:
      | filePath | {{ file to import }} |
    When I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then I should see "The \"sku\" attribute is unique, the value \"SKU-001\" was already read in this file"
    Then there should be 1 product
    And the product "SKU-001" should have the following values:
      | name        | Donec                                                             |
      | description | dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est |

  Scenario: Successfully update an existing product
    Given a "SKU-001" product
    Given the following product values:
      | product | attribute | value  |
      | SKU-001 | name      | FooBar |
    Given the following file to import:
      """
      sku;family;groups;categories;name;description
      SKU-001;Bag;;leather,travel;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And the following job "acme_product_import" configuration:
      | filePath | {{ file to import }} |
    When I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be 1 product
    And the product "SKU-001" should have the following values:
      | name        | Donec                                                             |
      | description | dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est |

  Scenario: Successfully import products through file upload
    Given the following file to import:
      """
      sku;family;groups;categories;name;description
      SKU-001;Bag;;leather,travel;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;Hat;;travel;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;Hat;;men;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      SKU-004;Hat;;men;nec;justo sit amet nulla. Donec non justo. Proin non massa
      SKU-005;Bag;;women,silk;non;tincidunt dui augue eu tellus. Phasellus elit pede, malesuada vel
      SKU-006;Bag;;leather;ipsum;Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam auctor,
      SKU-007;Hat;;;rutrum.;quis, pede. Praesent eu dui. Cum sociis natoque penatibus et
      SKU-008;Bag;;coton;ligula;urna et arcu imperdiet ullamcorper. Duis at lacus. Quisque purus
      SKU-009;Hat;;;porttitor;sagittis. Duis gravida. Praesent eu nulla at sem molestie sodales.
      SKU-010;Bag;;men,silk;non,;vestibulum nec, euismod in, dolor. Fusce feugiat. Lorem ipsum dolor
      """
    And the following job "acme_product_import" configuration:
      | uploadAllowed | yes |
    When I am on the "acme_product_import" import job page
    And I upload and import the file "{{ file to import }}"
    Then there should be 10 products

  Scenario: Successfully import products prices
    Given the following file to import:
      """
      sku;prices
      SKU-001;"100 EUR, 90 USD"
      SKU-002;50 EUR
      """
    And the following job "acme_product_import" configuration:
      | filePath | {{ file to import }} |
    When I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be 2 products
    And the product "SKU-001" should have the following value:
      | prices | 100.00 EUR, 90.00 USD |
    And the product "SKU-002" should have the following value:
      | prices | 50.00 EUR |

  Scenario: Successfully update existing products prices
    Given a "SKU-001" product
    And the following product values:
      | product | attribute | value            |
      | SKU-001 | Prices    | 100 EUR, 150 USD |
    And the following file to import:
      """
      sku;prices
      SKU-001;"100 EUR, 90 USD"
      """
    And the following job "acme_product_import" configuration:
      | filePath | {{ file to import }} |
    When I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be 1 products
    And the product "SKU-001" should have the following value:
      | prices | 100.00 EUR, 90.00 USD |
