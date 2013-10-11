Feature: Import categories
  In order to reuse the categories of my products
  As Julia
  I need to be able to import categories

  @javascript
  Scenario: Succesfully import categories
    Given the following jobs:
      | connector            | alias           | code                 | label                        | type   |
      | Akeneo CSV Connector | category_import | acme_category_import | Category import for Acme.com | import |
    And I am logged in as "Julia"
    And the following file to import:
    """
    code;parent;dynamic;label
    default;;;
    computers;;;en_US:Computers
    laptops;computers;;en_US:Laptops
    hard_drives;laptops;;"en_US:Hard drives"
    pc;computers;;en_US:PC
    """
    And the following job "acme_category_import" configuration:
      | element | property | value                |
      | reader  | filePath | {{ file to import }} |
    When I am on the "acme_category_import" import job page
    And I launch the import job
    Then there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |
