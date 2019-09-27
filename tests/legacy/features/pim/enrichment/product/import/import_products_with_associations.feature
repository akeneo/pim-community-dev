Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products with associations

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label-en_US | type    |
      | CROSS | Bag Cross   | RELATED |

  @javascript
  Scenario: Successfully import a csv file of products with associations
    Given the following CSV file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;winter_boots;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;sneakers;;winter_boots;;;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;sneakers;;sandals;;;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      """
    And I am logged in as "Julia"
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 3 products
    Given I edit the "SKU-001" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    Then I should see the text "2 product(s), 0 product model(s) and 1 group(s)"

  @pim-2445
  @javascript
  Scenario: Successfully skip associations with not existing product (owner side)
    Given the following CSV file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;unknown,travel;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And I am logged in as "Julia"
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 0 product
    And I should see the text "No product with identifier \"SKU-001\" has been found"

  @javascript
  Scenario: Successfully skip associations with no existing product (associated side)
    Given the following CSV file to import:
      """
      sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;;CROSS;SKU-002,SKU-003;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And I am logged in as "Julia"
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 1 product
    And I should see the text "Property \"associations\" expects a valid product identifier. The product does not exist, \"SKU-002\" given."

  @javascript
  Scenario: Successfully import a csv file with associations between invalid but existing products
    Given the following products:
      | sku     | family | name-en_US |
      | SKU-001 | boots  | Before     |
      | SKU-002 | boots  | Before     |
      | SKU-003 | boots  | Before     |
    And I am logged in as "Julia"
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
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    Then I should see the text "2 product(s), 0 product model(s) and 1 group(s)"
    And the english localizable value name of "SKU-001" should be "Before"

  @javascript
  Scenario: Successfully skip associations without modification
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    And I am logged in as "Julia"
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I add associations
    Then I check the rows "SKU-002"
    And I press the "Confirm" button in the popin
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

  @javascript
  Scenario: Successfully skip associations if no association defined
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    And I am logged in as "Julia"
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I add associations
    Then I check the rows "SKU-002"
    And I press the "Confirm" button in the popin
    And the following CSV file to import:
      """
      sku
      SKU-001
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 2 products
    And I should see the text "skipped product (no associations detected) 1"

  @javascript
  Scenario: Successfully remove associations
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    And I am logged in as "Julia"
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I add associations
    Then I check the rows "SKU-002"
    And I press the "Confirm" button in the popin
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
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    Then I should see the text "0 product(s), 0 product model(s) and 0 group(s)"

  @javascript
  Scenario: Successfully remove one association without removing the other
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    And I am logged in as "Julia"
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I add associations
    Then I check the rows "SKU-002"
    And I press the "Confirm" button in the popin
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Upsell" association type
    And I add associations
    Then I check the rows "SKU-002"
    And I press the "Confirm" button in the popin
    And the following CSV file to import:
      """
      sku;UPSELL-products
      SKU-001;
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    Then I should see the text "1 product(s), 0 product model(s) and 0 group(s)"
    And I visit the "Associations" column tab
    And I visit the "Upsell" association type
    Then I should see the text "0 product(s), 0 product model(s) and 0 group(s)"

  @jira https://akeneo.atlassian.net/browse/PIM-6019
  @javascript
  Scenario: Successfully import product without remove already existing associations when option "compare values" is set to false
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
    And I am logged in as "Julia"
    When I edit the "SKU-001" product
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I add associations
    And I check the rows "SKU-002"
    And I press the "Confirm" button in the popin
    And the following CSV file to import:
      """
      sku
      SKU-001
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath          | %file to import% |
      | enabledComparison | no               |
    And I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "skipped product (no associations detected)"

  @jira https://akeneo.atlassian.net/browse/PIM-6042
  Scenario: Successfully import product associations without removing already existing associations when option "compare values" is set to true
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
      | SKU-003 | sku-003    |
    And the following associations for the product "SKU-001":
      | type   | products |
      | X_SELL | SKU-002  |
      | UPSELL | SKU-002  |
    And the following CSV file to import:
      """
      sku;UPSELL-products
      SKU-001;SKU-003
      """
    When the products are imported via the job csv_footwear_product_import with options:
      | enabledComparison | yes |
    Then the product "SKU-001" should have the following associations:
      | type   | products |
      | X_SELL | SKU-002  |
      | UPSELL | SKU-003  |

  @jira https://akeneo.atlassian.net/browse/PIM-6071
  Scenario: Successfully import product associations with an attribute having the same code
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | sku-001    |
      | SKU-002 | sku-002    |
      | SKU-003 | sku-003    |
    Given the following association type:
      | code |
      | sku  |
    And the following CSV file to import:
      """
      sku;sku-products
      SKU-001;SKU-003
      """
    When the products are imported via the job csv_footwear_product_import with options:
      | enabledComparison | yes |
    Then the product "SKU-001" should have the following associations:
      | type   | products |
      | sku    | SKU-003  |
