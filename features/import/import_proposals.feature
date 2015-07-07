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
      | my-jacket3 | jackets    |

  Scenario: Create a new proposal
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;name-en_US;description-en_US-mobile;description-en_US-tablet;comment
    my-jacket;My jacket;My desc;My description;First comment
    """
    And the following job "clothing_product_draft_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_draft_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_draft_import" job to finish
    Then there should be 1 proposal
    And I should get the following proposal:
      | username                      | product   | result                                                                                                                                                                                                                                                                    |
      | clothing_product_draft_import | my-jacket | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket"}],"description":[{"locale":"en_US","scope":"mobile","data":"My desc"},{"locale":"en_US","scope":"tablet","data":"My description"}],"comment":[{"locale":null,"scope":null,"data":"First comment"}]}} |
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 1 element
    And I should see entity my-jacket

  Scenario: Update a proposal
    Given I am logged in as "Mary"
    And the following product drafts:
      | product   | status | author                        | result                                                                   |
      | my-jacket | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket"}]}} |
    And the following CSV file to import:
    """
    sku;description-en_US-mobile;description-en_US-tablet;comment
    my-jacket;My desc;My description;First comment
    """
    And the following job "clothing_product_draft_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_draft_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_draft_import" job to finish
    Then there should be 1 proposal
    And I should get the following proposal:
      | username                      | product   | result                                                                                                                                                                                                                                                                    |
      | clothing_product_draft_import | my-jacket | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket"}],"description":[{"locale":"en_US","scope":"mobile","data":"My desc"},{"locale":"en_US","scope":"tablet","data":"My description"}],"comment":[{"locale":null,"scope":null,"data":"First comment"}]}} |
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 1 element
    And I should see entity my-jacket

  Scenario: Update a proposal to add a new attribute
    Given I am logged in as "Mary"
    And the following product drafts:
      | product    | status | author                        | result                                                                                                                                        |
      | my-jacket  | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket"}],"description":[{"locale":"en_US","scope":"mobile","data":"My desc"}]}} |
      | my-jacket3 | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":null}]}}                                                                             |
    And the following CSV file to import:
    """
    sku;description-en_US-tablet;name-en_US
    my-jacket;My description;My jacket v2
    my-jacket2;;My new jacket
    my-jacket3;;My summer jacket
    """
    And the following job "clothing_product_draft_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_draft_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_draft_import" job to finish
    Then there should be 3 proposals
    And I should get the following proposal:
      | username                      | product    | result                                                                                                                                                                                                       |
      | clothing_product_draft_import | my-jacket  | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket v2"}],"description":[{"locale":"en_US","scope":"mobile","data":"My desc"},{"locale":"en_US","scope":"tablet","data":"My description"}]}} |
      | clothing_product_draft_import | my-jacket2 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My new jacket"}]}}                                                                                                                                 |
      | clothing_product_draft_import | my-jacket3 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My summer jacket"}]}}                                                                                                                              |
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 3 elements
    And I should see entity my-jacket, my-jacket2 and my-jacket3

  Scenario: Update a proposal to update old attributes and add new
    Given I am logged in as "Mary"
    And the following product drafts:
      | product    | status | author                        | result                                                                                                                                        |
      | my-jacket  | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket"}],"description":[{"locale":"en_US","scope":"mobile","data":"My desc"}]}} |
    And the following CSV file to import:
    """
    sku;description-en_US-tablet;description-fr_FR-mobile;description-fr_FR-tablet;description-en_US-mobile;name-en_US
    my-jacket;My description;Ma desc;Ma description;Desc;My jacket v2
    """
    And the following job "clothing_product_draft_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_draft_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_draft_import" job to finish
    Then there should be 1 proposal
    And I should get the following proposal:
      | username                      | product    | result                                                                                                                                                                                                                                                                                                                     |
      | clothing_product_draft_import | my-jacket  | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket v2"}],"description":[{"locale":"en_US","scope":"mobile","data":"Desc"},{"locale":"en_US","scope":"tablet","data":"My description"},{"locale":"fr_FR","scope":"mobile","data":"Ma desc"},{"locale":"fr_FR","scope":"tablet","data":"Ma description"}]}} |
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 1 element
    And I should see entity my-jacket

  Scenario: Redactor create a two different proposals via import and CLI
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;name-en_US
    my-jacket;My jacket
    """
    And the following job "clothing_product_draft_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_draft_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_draft_import" job to finish
    Then there should be 1 proposal
    When I should get the following products after apply the following updater to it:
      | product   | actions                                                                                               | result | username |
      | my-jacket | [{"type": "set_data", "field": "name", "data": "Wonderful jacket", "locale": "en_US", "scope": null}] | {}     | Mary     |
    Then there should be 2 proposals
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 1 element
    And I should see entity my-jacket
    And I should get the following proposals:
      | product   | username                      | result                                                                                 |
      | my-jacket | Mary                          | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Wonderful jacket"}]}} |
      | my-jacket | clothing_product_draft_import | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "My jacket"}]}}        |

  Scenario: Remove an optional attribute to a proposal
    Given I am logged in as "Mary"
    And the following product drafts:
      | product    | status | author                        | result                                                                                                                      |
      | my-jacket  | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket"}],"handmade":[{"locale":null,"scope":null,"data":0}]}} |
    And the following CSV file to import:
    """
    sku;handmade
    my-jacket;
    """
    And the following job "clothing_product_draft_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_draft_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_draft_import" job to finish
    Then there should be 1 proposal
    And I should get the following proposals:
      | product   | username                      | result                                                                          |
      | my-jacket | clothing_product_draft_import | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "My jacket"}]}} |

  Scenario: Remove a proposal if there is no diff
    Given I am logged in as "Mary"
    And the following product drafts:
      | product    | status | author                        | result                                                          |
      | my-jacket  | ready  | clothing_product_draft_import | {"values":{"handmade":[{"locale":null,"scope":null,"data":0}]}} |
    And the following CSV file to import:
    """
    sku;handmade
    my-jacket;
    """
    And the following job "clothing_product_draft_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_draft_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_draft_import" job to finish
    Then there should be 0 proposal
    And I should see "deleted 1"
