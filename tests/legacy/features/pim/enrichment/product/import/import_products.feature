Feature: Import products coming from an external application
  In order to enrich existing product information
  As an administrator
  I need to be able to import products regularly

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label-en_US | type    |
      | CROSS | Bag Cross   | RELATED |

  @critical @validate-migration
  Scenario: Successfully import a csv file of products
    Given the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;sneakers;;sandals;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      SKU-004;sneakers;;sandals;nec;justo sit amet nulla. Donec non justo. Proin non massa
      SKU-005;boots;CROSS;winter_boots,sandals;non;tincidunt dui augue eu tellus. Phasellus elit pede, malesuada vel
      SKU-006;boots;CROSS;winter_boots;ipsum;Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam auctor,
      SKU-007;sneakers;;;rutrum.;quis, pede. Praesent eu dui. Cum sociis natoque penatibus et
      SKU-008;boots;CROSS;winter_boots;ligula;urna et arcu imperdiet ullamcorper. Duis at lacus. Quisque purus
      SKU-009;sneakers;;;porttitor;sagittis. Duis gravida. Praesent eu nulla at sem molestie sodales.
      SKU-010;boots;CROSS;sandals;non,;vestibulum nec, euismod in, dolor. Fusce feugiat. Lorem ipsum dolor
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 10 products
    And the family of the product "SKU-006" should be "boots"
    And product "SKU-007" should be enabled
    And the english localizable value name of "SKU-001" should be "Donec"
    And the english tablet description of "SKU-002" should be "Pellentesque habitant morbi tristique senectus et netus et malesuada fames"

  Scenario: Successfully ignore duplicate unique data
    Given the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;boots;;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-001;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 1 product
    And the english localizable value name of "SKU-001" should be "Donec"
    And the english tablet description of "SKU-001" should be "dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est"

  Scenario: Successfully import product by ignoring attributes that are not part of the family
    Given the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet;comment
      SKU-001;boots;;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est;This comment should not be imported
      """
    When the products are imported via the job csv_footwear_product_import
    Then the product "SKU-001" should not have the following values:
      | comment |

  Scenario: Successfully update an existing product
    Given the following product:
      | sku     | name-en_US |
      | SKU-001 | FooBar     |
    And the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;boots;;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 1 product
    And the english localizable value name of "SKU-001" should be "Donec"
    And the english tablet description of "SKU-001" should be "dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est"

  Scenario: Successfully update existing products prices
    Given the following product:
      | sku     | price            |
      | SKU-001 | 100 EUR, 150 USD |
    And the following CSV file to import:
      """
      sku;price
      SKU-001;"100 EUR, 90 USD"
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 1 products
    And the product "SKU-001" should have the following value:
      | price | 100.00 EUR, 90.00 USD |

  Scenario: Successfully import products metrics
    Given the following CSV file to import:
      """
      sku;length
      SKU-001;4000 CENTIMETER
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 1 products
    And the product "SKU-001" should have the following value:
      | length | 4000.0000 CENTIMETER |

  Scenario: Successfully import products metrics splitting the data and unit
    Given the following CSV file to import:
      """
      sku;length;length-unit
      SKU-001;4000;CENTIMETER
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 1 products
    And the product "SKU-001" should have the following value:
      | length | 4000.0000 CENTIMETER |

  Scenario: Successfully skip a product without modification
    Given the following product:
      | sku     | name-en_US | description-en_US-tablet                                          | family | categories   |
      | SKU-001 | FooBar     | dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est | boots  | winter_boots |
    And the following CSV file to import:
      """
      sku;family;categories;name-en_US;description-en_US-tablet
      SKU-001;boots;winter_boots;FooBar;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 1 product
    And there should be 1 product skipped because there is no difference

  Scenario: Successfully import a csv file with sku and family column
    Given the following CSV file to import:
      """
      sku;family
      SKU-001;boots
      SKU-002;sneakers
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 2 products

  Scenario: Successfully import a csv file of products without enabled column default yes
    Given the following product:
      | sku     | name-en_US | description-en_US-tablet                    | enabled |
      | SKU-001 | John Deere | Best of tractors                            | no      |
      | SKU-002 | Class      | Leader in agricultural harvesting equipment | yes     |
      | SKU-003 | Renault    | French Tractors                             | no      |
    And the following CSV file to import:
      """
      sku;name-en_US;description-en_US-tablet
      SKU-001;John Deere;Go fast with John Deere
      SKU-002;Class;Ride with Class
      SKU-003;Renault;French touch for tractors
      SKU-004;New Holland;Faster tractors
      """
    When the products are imported via the job csv_footwear_product_import with options:
      | enabled | yes |
    Then there should be 4 products
    And product "SKU-001" should be disabled
    And product "SKU-002" should be enabled
    And product "SKU-003" should be disabled
    And product "SKU-004" should be enabled

  @jira https://akeneo.atlassian.net/browse/PIM-6085
  @javascript
  Scenario: Successfully import product associations with modified column name
    Given I am logged in as "Julia"
    And the following CSV file to import:
      """
      sku;family;groupes;catégories;name-en_US;description-en_US-tablet;price;size;color
      SKU-001;boots;similar_boots;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est;"100 EUR, 90 USD";40;
      SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames;"100 EUR, 90 USD";37;red
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath          | %file to import% |
      | enabledComparison | yes              |
      | categoriesColumn  | catégories       |
      | groupsColumn      | groupes          |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should not see the text "The fields \"catégories, famille, groupes\" do not exist"
    Then there should be 2 products
