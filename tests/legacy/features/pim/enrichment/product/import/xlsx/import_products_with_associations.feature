@javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products with associations

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label-en_US | type    |
      | CROSS | Bag Cross   | RELATED |
    And I am logged in as "Julia"

  Scenario: Successfully import a xlsx file of products with associations
    Given the following XLSX file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;winter_boots;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;sneakers;;winter_boots;;;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;sneakers;;sandals;;;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 3 products
    Given I edit the "SKU-001" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    Then I should see the text "2 product(s), 0 product model(s) and 1 group(s)"

  Scenario: Successfully skip associations with not existing product (owner side)
    Given the following XLSX file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;unknown,travel;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 0 product
    And I should see the text "No product with identifier \"SKU-001\" has been found"

  Scenario: Successfully skip associations with no existing product (associated side)
    Given the following XLSX file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 1 product
    And I should see the text "Property \"associations\" expects a valid product identifier. The product does not exist, \"SKU-002\" given."

  Scenario: Successfully import a xlsx file with associations between invalid but existing products
    Given the following products:
      | sku     | family | name-en_US |
      | SKU-001 | boots  | Before     |
      | SKU-002 | boots  | Before     |
      | SKU-003 | boots  | Before     |
    Given the following XLSX file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US
      SKU-001;boots;CROSS;unknown;CROSS;SKU-002,SKU-003;After
      SKU-002;sneakers;;unknown;;;After
      SKU-003;sneakers;;unknown;;;After
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 3 products
    Given I edit the "SKU-001" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    Then I should see the text "2 product(s), 0 product model(s) and 1 group(s)"
    And the english localizable value name of "SKU-001" should be "Before"

  Scenario: Successfully skip associations without modification
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I add associations
    Then I check the rows "SKU-002"
    And I press the "Confirm" button in the popin
    And the following XLSX file to import:
      """
      sku;X_SELL-products
      SKU-001;SKU-002
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 2 products
    And I should see the text "skipped product (no differences) 1"

  Scenario: Successfully remove associations
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I add associations
    Then I check the rows "SKU-002"
    And I press the "Confirm" button in the popin
    And the following XLSX file to import:
      """
      sku;X_SELL-products
      SKU-001;
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    Then I should see the text "0 product(s), 0 product model(s) and 0 group(s)"

  @jira https://akeneo.atlassian.net/browse/PIM-5696
  Scenario: Successfully import products with associations and numeric value as SKU
    Given the following XLSX file to import:
      """
      sku;family;groups;X_SELL-groups
      123;boots;CROSS;CROSS
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am on the "xlsx_footwear_product_import" import job page
    When I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    And I edit the "123" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    Then I should see the text "0 product(s), 0 product model(s) and 1 group(s)"
