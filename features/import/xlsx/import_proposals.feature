@javascript
Feature: Import proposals with XLSX files
  In order generate proposals
  As a redactor
  I need to be able to import proposals

  Background:
    Given the "clothing" catalog configuration
    And the following product:
      | sku        | categories |
      | my-jacket  | jackets    |
      | my-jacket2 | jackets    |
      | my-jacket3 | jackets    |

  Scenario: Create a new proposal
    Given I am logged in as "Mary"
    And the following XLSX file to import:
    """
    sku;name-en_US;description-en_US-mobile;description-en_US-tablet;comment
    my-jacket;My jacket;My desc;My description;First comment
    """
    And the following job "xlsx_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "xlsx_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "xlsx_clothing_product_proposal_import" job to finish
    And I logout
    And I am logged in as "Julia"
    When I am on the proposals page
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product   | author                                | attribute   | locale | scope  | original | new            |
      | my-jacket | xlsx_clothing_product_proposal_import | name        | en_US  |        |          | My jacket      |
      | my-jacket | xlsx_clothing_product_proposal_import | description | en_US  | mobile |          | My desc        |
      | my-jacket | xlsx_clothing_product_proposal_import | description | en_US  | tablet |          | My description |
      | my-jacket | xlsx_clothing_product_proposal_import | comment     |        |        |          | First comment  |

  Scenario: Import proposal with media
    Given I am logged in as "Mary"
    And I am on the "xlsx_clothing_product_proposal_import" import job page
    When I upload and import the file "proposal_import_xlsx.zip"
    And I wait for the "xlsx_clothing_product_proposal_import" job to finish
    Then I should see the text "read lines 3"
    And I should see the text "created proposal 3"
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    Then I should see the following proposals:
      | product   | author                                | attribute   | original | new          |
      | my-jacket | xlsx_clothing_product_proposal_import | side_view   |          | jack_003.png |
    And I should see entity my-jacket2
    And I should see entity my-jacket3
