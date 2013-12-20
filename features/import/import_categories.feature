Feature: Import categories
  In order to reuse the categories of my products
  As Julia
  I need to be able to import categories

  @javascript
  Scenario: Succesfully import categories
    Given the "default" catalog configuration
    And the following jobs:
      | connector            | alias           | code                 | label                        | type   |
      | Akeneo CSV Connector | category_import | acme_category_import | Category import for Acme.com | import |
    And I am logged in as "Julia"
    And the following file to import:
    """
    code;parent;label-en_US
    default;;
    computers;;Computers
    laptops;computers;Laptops
    hard_drives;laptops;Hard drives
    pc;computers;PC
    """
    And the following job "acme_category_import" configuration:
      | filePath | {{ file to import }} |
    When I am on the "acme_category_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |
