@javascript
Feature: Import proposals
  In order generate proposals
  As a redactor
  I need to be able to import proposals

  Background:
    Given the "clothing" catalog configuration
    And the following product:
      | sku        | categories |
      | my-jacket  | jackets    |
      | my-jacket2 | jackets    |

  Scenario: Fail to create a proposal if product does not exist
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;description-en_US-mobile;description-en_US-tablet;comment
    not-found-product;My desc;My description;First comment
    my-jacket2;;Description;
    """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 1 proposal
    And I should see:
    """
    Product "not-found-product" does not exist
    """
    And I should see "skipped 1"

  Scenario: Ignore field which is not an attribute
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;enable;description-en_US-mobile
    my-jacket;1;My desc
    my-jacket2;0;My desc
    """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 2 proposals

  Scenario: Fail to create a proposal if data does not contain an identifier column
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    description-en_US-mobile;description-en_US-tablet;comment
    My desc;My description;First comment
    my-jacket2;;Description;
    """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 0 proposal
    And I should see:
    """
    Identifier property "sku" is expected
    """
    And I should see "Status: FAILED"

  Scenario: Skip proposal if there is no diff between product and proposal
    Given I am logged in as "Mary"
    And the following product values:
      | product   | attribute   | value          | locale | scope  |
      | my-jacket | description | My description | en_US  | tablet |
      | my-jacket | description | My desc        | en_US  | mobile |
      | my-jacket | comment     | First comment  |        |        |
    And the following CSV file to import:
    """
    sku;description-en_US-mobile;description-en_US-tablet;comment
    my-jacket;My desc;My description;First comment
    """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 0 proposal
    And I should see "No diff between current product and this proposal"
    And I should see "skipped proposal (no differences) 1"

  Scenario: Skip a proposal when done on a non existing product
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;description-en_US-mobile;description-en_US-tablet;comment
    unknow;My desc;My description;First comment
    """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then I should see "skipped 1"
    And I should see "Product \"unknow\" does not exist"
