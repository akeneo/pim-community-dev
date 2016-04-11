@javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products with associations

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label     | type    |
      | CROSS | Bag Cross | RELATED |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of products with associations
    Given the following CSV file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;winter_boots;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;sneakers;;winter_boots;;;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;sneakers;;sandals;;;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 3 products
    Given I edit the "SKU-001" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    Then I should see the text "2 products and 1 groups"

  @pim-2445
  Scenario: Successfully skip associations with not existing product (owner side)
    Given the following CSV file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;unknown,travel;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 0 product
    And I should see the text "No product with identifier \"SKU-001\" has been found"

  Scenario: Successfully skip associations with no existing product (associated side)
    Given the following CSV file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 1 product
    And I should see the text "Attribute or field \"associations\" expects existing product identifier as data, \"SKU-002\" given"

  Scenario: Successfully import a csv file with associations between invalid but existing products
    Given the following products:
      | sku     | family | name-en_US |
      | SKU-001 | boots  | Before     |
      | SKU-002 | boots  | Before     |
      | SKU-003 | boots  | Before     |
    Given the following CSV file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US
      SKU-001;boots;CROSS;unknown;CROSS;SKU-002,SKU-003;After
      SKU-002;sneakers;;unknown;;;After
      SKU-003;sneakers;;unknown;;;After
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 3 products
    Given I edit the "SKU-001" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    Then I should see the text "2 products and 1 groups"
    And the english name of "SKU-001" should be "Before"

  Scenario: Successfully skip associations without modification
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    When I edit the "SKU-001" product
    And I visit the "Associations" tab
    And I visit the "Cross sell" group
    Then I check the rows "SKU-002"
    And I save the product
    And the following CSV file to import:
      """
      sku;X_SELL-products
      SKU-001;SKU-002
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 2 products
    And I should see the text "skipped product (no differences) 1"

  Scenario: Successfully remove associations
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    When I edit the "SKU-001" product
    And I visit the "Associations" tab
    And I visit the "Cross sell" group
    Then I check the rows "SKU-002"
    And I save the product
    And the following CSV file to import:
      """
      sku;X_SELL-products
      SKU-001;
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    When I edit the "SKU-001" product
    And I visit the "Associations" tab
    And I visit the "Cross sell" group
    Then I should see the text "0 products and 0 groups"
