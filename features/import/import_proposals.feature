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
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And I logout
    And I am logged in as "Julia"
    When I am on the proposals page
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product   | author                               | attribute   | locale | scope  | original | new            |
      | my-jacket | csv_clothing_product_proposal_import | name        | en_US  |        |          | My jacket      |
      | my-jacket | csv_clothing_product_proposal_import | description | en_US  | mobile |          | My desc        |
      | my-jacket | csv_clothing_product_proposal_import | description | en_US  | tablet |          | My description |
      | my-jacket | csv_clothing_product_proposal_import | comment     |        |        |          | First comment  |

  Scenario: Create a new proposals and be notified
    Given Mary proposed the following change to "my-jacket3":
      | field | value        |
      | SKU   | third-jacket |
    And the following CSV file to import:
    """
    sku;name-en_US;description-en_US-mobile
    my-jacket;Jacket;Description
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Mary"
    When I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    When I logout
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 2 new notifications
    And I should see notification:
      | type | message                                                 |
      | add  | Mary Smith has sent proposals to review from job import |
    When I click on the notification "Mary Smith has sent proposals to review from job import"
    Then I should be on the proposals index page
    And the grid should contain 1 element
    And I should see the following proposal:
      | product   | author                               | attribute   | original | new         |
      | my-jacket | csv_clothing_product_proposal_import | name        |          | Jacket      |
      | my-jacket | csv_clothing_product_proposal_import | description |          | Description |

  Scenario: Import proposal with media
    Given I am logged in as "Mary"
    And I am on the "csv_clothing_product_proposal_import" import job page
    When I upload and import the file "proposal_import.zip"
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    Then I should see the text "read lines 3"
    And I should see the text "created proposal 3"
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    Then I should see the following proposals:
      | product   | author                               | attribute | original | new          |
      | my-jacket | csv_clothing_product_proposal_import | side_view |          | jack_003.png |
    And I should see entity my-jacket2
    And I should see entity my-jacket3

  Scenario: Update a proposal
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;name-en_US
    my-jacket;My jacket
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And the following CSV file to import:
    """
    sku;description-en_US-mobile;description-en_US-tablet;comment
    my-jacket;My desc;My description;First comment
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And I logout
    And I am logged in as "Julia"
    When I am on the proposals page
    Then the grid should contain 1 element
    Then I should see the following proposals:
      | product   | author                               | attribute   | locale | scope  | original | new            |
      | my-jacket | csv_clothing_product_proposal_import | name        | en_US  |        |          | My jacket      |
      | my-jacket | csv_clothing_product_proposal_import | description | en_US  | mobile |          | My desc        |
      | my-jacket | csv_clothing_product_proposal_import | description | en_US  | tablet |          | My description |
      | my-jacket | csv_clothing_product_proposal_import | comment     |        |        |          | First comment  |

  Scenario: Update a proposal to add a new attribute
    Given I am logged in as "Mary"
    And the following product drafts:
      | product    | status | author                               | result                                                                                                                                        |
      | my-jacket  | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket"}],"description":[{"locale":"en_US","scope":"mobile","data":"My desc"}]}} |
      | my-jacket3 | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":null}]}}                                                                             |
    And the following CSV file to import:
    """
    sku;description-en_US-tablet;name-en_US
    my-jacket;My description;My jacket v2
    my-jacket2;;My new jacket
    my-jacket3;;My summer jacket
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And I logout
    And I am logged in as "Julia"
    When I am on the proposals page
    Then the grid should contain 3 elements
    And I should see the following proposals:
      | product    | author                               | attribute   | locale | scope  | original | new              |
      | my-jacket  | csv_clothing_product_proposal_import | name        | en_US  |        |          | My jacket v2     |
      | my-jacket  | csv_clothing_product_proposal_import | description | en_US  | mobile |          | My desc          |
      | my-jacket  | csv_clothing_product_proposal_import | description | en_US  | tablet |          | My description   |
      | my-jacket2 | csv_clothing_product_proposal_import | name        | en_US  |        |          | My new jacket    |
      | my-jacket3 | csv_clothing_product_proposal_import | name        | en_US  |        |          | My summer jacket |

  Scenario: Update a proposal to update old attributes and add new
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;name-en_US;description-en_US-mobile
    my-jacket;My jacket;My desc
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And the following CSV file to import:
    """
    sku;description-en_US-tablet;description-fr_FR-mobile;description-fr_FR-tablet;description-en_US-mobile;name-en_US
    my-jacket;My description;Ma desc;Ma description;Desc;My jacket v2
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And I logout
    And I am logged in as "Julia"
    When I am on the proposals page
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product   | author                               | attribute   | locale | scope  | original | new            |
      | my-jacket | csv_clothing_product_proposal_import | name        | en_US  |        |          | My jacket v2   |
      | my-jacket | csv_clothing_product_proposal_import | description | en_US  | mobile |          | Desc           |
      | my-jacket | csv_clothing_product_proposal_import | description | en_US  | tablet |          | My description |
      | my-jacket | csv_clothing_product_proposal_import | description | fr_FR  | mobile |          | Ma desc        |
      | my-jacket | csv_clothing_product_proposal_import | description | fr_FR  | tablet |          | Ma description |

  Scenario: Redactor create two different proposals via import and CLI
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;name-en_US
    my-jacket;My jacket
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And I should get the following product drafts after apply the following updater to it:
      | product   | actions                                                                                               | result | username |
      | my-jacket | [{"type": "set_data", "field": "name", "data": "Wonderful jacket", "locale": "en_US", "scope": null}] | {}     | Mary     |
    And I logout
    And I am logged in as "Julia"
    When I am on the proposals page
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product   | author                               | attribute | locale | original | new       |
      | my-jacket | csv_clothing_product_proposal_import | name      | en_US  |          | My jacket |

  Scenario: Remove an optional attribute to a proposal
    Given I am logged in as "Mary"
    And the following product drafts:
      | product   | status | author                               | result                                                                                                                      |
      | my-jacket | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My jacket"}],"handmade":[{"locale":null,"scope":null,"data":0}]}} |
    And the following CSV file to import:
    """
    sku;handmade
    my-jacket;
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And I logout
    And I am logged in as "Julia"
    When I am on the proposals page
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product   | author                               | attribute | locale | original | new       |
      | my-jacket | csv_clothing_product_proposal_import | name      | en_US  |          | My jacket |

  Scenario: Remove a proposal if there is no diff
    Given I am logged in as "Mary"
    And the following product drafts:
      | product   | status | author                               | result                                                          |
      | my-jacket | ready  | csv_clothing_product_proposal_import | {"values":{"handmade":[{"locale":null,"scope":null,"data":0}]}} |
    And the following CSV file to import:
    """
    sku;handmade
    my-jacket;
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    Then there should be 0 proposal
    And I should see the text "deleted proposal 1"

  Scenario: Update a proposal with same file format than product import
    Given I am logged in as "Mary"
    And the following CSV file to import:
    """
    sku;name-en_US
    my-jacket;My jacket
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And the following CSV file to import:
    """
    sku;description-en_US-mobile;description-en_US-tablet;comment
    my-jacket;My desc;My description;First comment
    """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And I logout
    And I am logged in as "Julia"
    When I am on the proposals page
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product   | author                               | attribute   | locale | scope  | original | new            |
      | my-jacket | csv_clothing_product_proposal_import | name        | en_US  |        |          | My jacket      |
      | my-jacket | csv_clothing_product_proposal_import | description | en_US  | mobile |          | My desc        |
      | my-jacket | csv_clothing_product_proposal_import | description | en_US  | tablet |          | My description |
      | my-jacket | csv_clothing_product_proposal_import | comment     |        |        |          | First comment  |
