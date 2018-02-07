Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label-en_US | type    |
      | CROSS | Bag Cross   | RELATED |

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
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 10 products
    And the family of the product "SKU-006" should be "boots"
    And product "SKU-007" should be enabled
    And the english localizable value name of "SKU-001" should be "Donec"
    And the english tablet description of "SKU-002" should be "Pellentesque habitant morbi tristique senectus et netus et malesuada fames"

  Scenario: Successfully import a csv file of product with carriage return in product description
    Given the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;winter_boots;Donec;"dictum magna. Ut tincidunt
      orci quis lectus.

      Nullam suscipit,
      est

      "
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 1 products
    And the english tablet description of "SKU-001" should be "dictum magna. Ut tincidunt|NL|orci quis lectus.|NL||NL|Nullam suscipit,|NL|est|NL||NL|"

  Scenario: Successfully ignore duplicate unique data
    Given the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;boots;;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-001;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 1 product
    And the english localizable value name of "SKU-001" should be "Donec"
    And the english tablet description of "SKU-001" should be "dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est"

  Scenario: Successfully import product by ignoring attributes that are not part of the family
    Given the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet;comment
      SKU-001;boots;;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est;This comment should not be imported
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
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
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 1 product
    And the english localizable value name of "SKU-001" should be "Donec"
    And the english tablet description of "SKU-001" should be "dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est"

  @javascript
  Scenario: Successfully import products through file upload
    Given I am logged in as "Julia"
    And the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;boots;;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;sneakers;;sandals;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      SKU-004;sneakers;;sandals;nec;justo sit amet nulla. Donec non justo. Proin non massa
      SKU-005;boots;;winter_boots;non;tincidunt dui augue eu tellus. Phasellus elit pede, malesuada vel
      SKU-006;boots;;winter_boots;ipsum;Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam auctor,
      SKU-007;sneakers;;;rutrum.;quis, pede. Praesent eu dui. Cum sociis natoque penatibus et
      SKU-008;boots;;sandals;ligula;urna et arcu imperdiet ullamcorper. Duis at lacus. Quisque purus
      SKU-009;sneakers;;;porttitor;sagittis. Duis gravida. Praesent eu nulla at sem molestie sodales.
      SKU-010;boots;;sandals,winter_boots;non,;vestibulum nec, euismod in, dolor. Fusce feugiat. Lorem ipsum dolor
      """
    And the following job "csv_footwear_product_import" configuration:
      | uploadAllowed | yes |
    When I am on the "csv_footwear_product_import" import job page
    And I upload and import the file "%file to import%"
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 10 products

  Scenario: Successfully import products prices
    Given the following CSV file to import:
      """
      sku;price
      SKU-001;"100 EUR, 90 USD"
      SKU-002;50 EUR
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 2 products
    And the product "SKU-001" should have the following value:
      | price | 100.00 EUR, 90.00 USD |
    And the product "SKU-002" should have the following value:
      | price | 50.00 EUR |

  Scenario: Successfully update existing products prices
    Given the following product:
      | sku     | price            |
      | SKU-001 | 100 EUR, 150 USD |
    And the following CSV file to import:
      """
      sku;price
      SKU-001;"100 EUR, 90 USD"
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 1 products
    And the product "SKU-001" should have the following value:
      | price | 100.00 EUR, 90.00 USD |

  Scenario: Successfully import products metrics
    Given the following CSV file to import:
      """
      sku;length
      SKU-001;4000 CENTIMETER
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 1 products
    And the product "SKU-001" should have the following value:
      | length | 4000.0000 CENTIMETER |

  Scenario: Successfully import products metrics splitting the data and unit
    Given the following CSV file to import:
      """
      sku;length;length-unit
      SKU-001;4000;CENTIMETER
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
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
    And I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 1 product
    And there should be 1 product skipped because there is no difference

  Scenario: Successfully import products with attributes with full numeric codes
    Given the following family:
      | code      | attributes           |
      | my_family | name,123,description |
    And the following CSV file to import:
      """
      sku;123;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;aaa;my_family;;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;bbb;my_family;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then there should be 2 product
    And the product "SKU-001" should have the following values:
      | name-en_US | Donec |
      | 123        | aaa   |
    And the product "SKU-002" should have the following values:
      | name-en_US | Donex |
      | 123        | bbb   |

  Scenario: Successfully import a csv file with sku and family column
    Given the following CSV file to import:
      """
      sku;family
      SKU-001;boots
      SKU-002;sneakers
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
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
    When I import it via the job "csv_footwear_product_import" as "Julia" with options:
      | enabled | yes |
    And I wait for this job to finish
    Then there should be 4 products
    And product "SKU-001" should be disabled
    And product "SKU-002" should be enabled
    And product "SKU-003" should be disabled
    And product "SKU-004" should be enabled

  Scenario: Successfully import a csv file of products without enabled column default no
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
    When I import it via the job "csv_footwear_product_import" as "Julia" with options:
      | enabled | no |
    And I wait for this job to finish
    Then there should be 4 products
    And product "SKU-001" should be disabled
    And product "SKU-002" should be enabled
    And product "SKU-003" should be disabled
    And product "SKU-004" should be disabled

  @javascript
  Scenario: Successfully import products when category code is integer
    Given I am logged in as "Julia"
    And the following products:
      | sku    |
      | jacket |
    And I am on the category "2014_collection" node creation page
    And I fill in the following information:
      | Code | 123 |
    And I save the category
    And the following CSV file to import:
      """
      sku;categories
      jacket;123
      """
    When I import it via the job "csv_footwear_product_import" as "Julia"
    And I wait for this job to finish
    Then the category of the product "jacket" should be "123"

  @javascript
  Scenario: Successfully import a csv file of products and the completeness should be computed
    Given I am logged in as "Julia"
    And the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet;price;size;color
      SKU-001;boots;similar_boots;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est;"100 EUR, 90 USD";40;
      SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames;"100 EUR, 90 USD";37;red
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And I am on the "SKU-001" product page
    When I visit the "Completeness" column tab
    And I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | en_US  | warning | 4              | 55%   |
      | mobile  | en_US  | warning | 1              | 80%   |
    And I am on the "SKU-002" product page
    When I visit the "Completeness" column tab
    And I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | en_US  | warning | 3              | 66%   |
      | mobile  | en_US  | success | 0              | 100%  |

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
