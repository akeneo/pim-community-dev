@proposal-feature-enabled
Feature: Import proposals
  In order generate proposals
  As a redactor
  I need to be able to import proposals

  Background:
    Given the "default" catalog configuration
    And the following categories:
      | code    |
      | jackets |
    And the following product category accesses:
      | product category | user group | access |
      | jackets          | Redactor   | edit   |
    And the following jobs:
      | connector            | type   | alias                       | code                        | label                       |
      | Akeneo CSV Connector | import | csv_product_proposal_import | csv_product_proposal_import | CSV product proposal import |
    And the following attributes:
      | code         | type                 | localizable | scopable | group | allowed_extensions |
      | name         | pim_catalog_text     | 1           | 0        | other |                    |
      | description  | pim_catalog_text     | 1           | 1        | other |                    |
      | comment      | pim_catalog_textarea | 0           | 0        | other |                    |
      | manufacturer | pim_catalog_text     | 0           | 0        | other |                    |
      | side_view    | pim_catalog_image    | 0           | 0        | other | png                |
      | handmade     | pim_catalog_boolean  | 0           | 0        | other |                    |
    And the following product:
      | sku        | categories |
      | my-jacket  | jackets    |
      | my-jacket2 | jackets    |
      | my-jacket3 | jackets    |

  Scenario: Create a new proposal
    Given the following CSV file to import:
      """
      sku;name-fr_FR;description-fr_FR-mobile;description-fr_FR-ecommerce;comment
      my-jacket;My jacket;Ma desc;Ma description;First comment
      """
    When Mary imports "product proposal" via the job csv_product_proposal_import
    Then the proposal for product "my-jacket" and author Mary should be:
      | field                       | value          |
      | name-fr_FR                  | My jacket      |
      | description-fr_FR-mobile    | Ma desc        |
      | description-fr_FR-ecommerce | Ma description |
      | comment                     | First comment  |

  Scenario: Update a proposal
    Given the following CSV file to import:
      """
      sku;name-fr_FR
      my-jacket;My jacket
      """
    When Mary imports "product proposal" via the job csv_product_proposal_import
    Then the proposal for product "my-jacket" and author Mary should be:
      | field      | value     |
      | name-fr_FR | My jacket |
    Given the following CSV file to import:
      """
      sku;description-fr_FR-mobile;description-fr_FR-ecommerce;comment
      my-jacket;Ma desc;Ma description;First comment
      """
    When Mary imports "product proposal" via the job csv_product_proposal_import
    Then the proposal for product "my-jacket" and author Mary should be:
      | field                       | value          |
      | name-fr_FR                  | My jacket      |
      | description-fr_FR-mobile    | Ma desc        |
      | description-fr_FR-ecommerce | Ma description |
      | comment                     | First comment  |

  Scenario: Update a proposal to update old attributes and add new
    Given the following product drafts:
      | product    | status | source | source_label | author | author_label | result                                                                                                                                        |
      | my-jacket  | ready  | pim    | PIM          | Mary   | Mary Smith   | {"values":{"name":[{"locale":"fr_FR","scope":null,"data":"My jacket"}],"description":[{"locale":"fr_FR","scope":"mobile","data":"Ma desc"}]}} |
      | my-jacket3 | ready  | pim    | PIM          | Mary   | Mary Smith   | {"values":{}}                                                                             |
    And the following CSV file to import:
      """
      sku;description-fr_FR-ecommerce;name-fr_FR;manufacturer
      my-jacket;Ma description;My jacket v2;H_and_M
      my-jacket2;;My new jacket;Nike
      my-jacket3;;My summer jacket;
      """
    When Mary imports "product proposal" via the job csv_product_proposal_import
    Then the proposal for product "my-jacket" and author Mary should be:
      | field                       | value          |
      | name-fr_FR                  | My jacket v2   |
      | description-fr_FR-mobile    | Ma desc        |
      | description-fr_FR-ecommerce | Ma description |
      | manufacturer                | H_and_M        |
    And the proposal for product "my-jacket2" and author Mary should be:
      | field        | value         |
      | name-fr_FR   | My new jacket |
      | manufacturer | Nike          |
    And the proposal for product "my-jacket3" and author Mary should be:
      | field      | value            |
      | name-fr_FR | My summer jacket |

  Scenario: Remove a proposal if there is no diff
    Given the following product drafts:
      | product   | status | source | source_label | author | author_label | result                                                          |
      | my-jacket | ready  | pim    | PIM          | Mary   | Mary Smith   | {"values":{"handmade":[{"locale":null,"scope":null,"data":false}]}} |
    Then there is one proposal for product "my-jacket" and author Mary
    And the following CSV file to import:
      """
      sku;handmade
      my-jacket;
      """
    When Mary imports "product proposal" via the job csv_product_proposal_import
    Then there is no proposal for product "my-jacket" and author Mary

  Scenario: Remove an optional attribute to a proposal
    Given the following product drafts:
      | product   | status | source | source_label | author | author_label | result                                                                                                                      |
      | my-jacket | ready  | pim    | PIM          | Mary   | Mary Smith   | {"values":{"name":[{"locale":"fr_FR","scope":null,"data":"My jacket"}],"handmade":[{"locale":null,"scope":null,"data":false}]}} |
    And the following CSV file to import:
      """
      sku;handmade
      my-jacket;
      """
    When Mary imports "product proposal" via the job csv_product_proposal_import
    And the proposal for product "my-jacket" and author Mary should be:
      | field      | value     |
      | name-fr_FR | My jacket |

  Scenario: Import proposal with media
    When Mary imports proposal_import.zip file for "product proposal" via the job csv_product_proposal_import
    Then the proposal for product "my-jacket" and author Mary should be:
      | field     | value        |
      | side_view | jack_003.png |
    And the proposal for product "my-jacket2" and author Mary should be:
      | field     | value        |
      | side_view | jack_002.png |
    And the proposal for product "my-jacket3" and author Mary should be:
      | field     | value        |
      | side_view | jack_001.png |
