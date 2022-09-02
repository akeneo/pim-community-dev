@proposal-feature-enabled
Feature: Import product model proposals
  In order generate product model proposals
  As a redactor
  I need to be able to import product model proposals

  Background:
    Given the "default" catalog configuration
    And the following categories:
      | code    |
      | jackets |
    And the following product category accesses:
      | product category | user group | access |
      | jackets          | Redactor   | edit   |
    And the following jobs:
      | connector            | type   | alias                             | code                              | label                             |
      | Akeneo CSV Connector | import | csv_product_model_proposal_import | csv_product_model_proposal_import | CSV product model proposal import |
    And the following attributes:
      | code         | type                     | localizable | scopable | group | allowed_extensions |
      | name         | pim_catalog_text         | 1           | 0        | other |                    |
      | description  | pim_catalog_text         | 1           | 1        | other |                    |
      | comment      | pim_catalog_textarea     | 0           | 0        | other |                    |
      | manufacturer | pim_catalog_text         | 0           | 0        | other |                    |
      | supplier     | pim_catalog_text         | 0           | 0        | other |                    |
      | side_view    | pim_catalog_image        | 0           | 0        | other | png                |
      | handmade     | pim_catalog_boolean      | 0           | 0        | other |                    |
      | color        | pim_catalog_simpleselect | 0           | 0        | other |                    |
      | size         | pim_catalog_simpleselect | 0           | 0        | other |                    |
    And the following "color" attribute options: red and black
    And the following "size" attribute options: s and l
    And the following family:
      | code    | requirements-ecommerce | requirements-mobile | attributes                                                    |
      | jackets | sku                    | sku                 | color,size,sku,name,description,comment,manufacturer,handmade |
    And the following family variants:
      | code             | family  | variant-axes_1 | variant-attributes_1 | variant-axes_2 | variant-attributes_2 |
      | jacket_two_level | jackets | size           | size,comment         | color          | color,sku            |
    And the following root product models:
      | code      | categories | family_variant   |
      | my-jacket | jackets    | jacket_two_level |
    And the following sub product models:
      | code        | parent    | size | handmade |
      | my-jacket-s | my-jacket | s    | 1        |
      | my-jacket-l | my-jacket | l    | 1        |

  Scenario: Create a new root product model proposals and ignore values from root product model
    Given the following CSV file to import:
      """
      code;name-fr_FR;description-fr_FR-mobile;description-fr_FR-ecommerce;comment
      my-jacket;My jacket;Ma desc;Ma description;First comment
      """
    When Mary imports "product model proposal" via the job csv_product_model_proposal_import
    Then the proposal for product model "my-jacket" and author Mary should be:
      | field                       | value          |
      | name-fr_FR                  | My jacket      |
      | description-fr_FR-mobile    | Ma desc        |
      | description-fr_FR-ecommerce | Ma description |

  Scenario: Create new sub product model proposals and ignore values from root product model
    Given the following CSV file to import:
      """
      code;name-fr_FR;description-fr_FR-mobile;description-fr_FR-ecommerce;comment
      my-jacket-s;My jacket 2;Autre desc;Autre description;Autre commentaire
      """
    When Mary imports "product model proposal" via the job csv_product_model_proposal_import
    Then the proposal for product model "my-jacket-s" and author Mary should be:
      | field   | value             |
      | comment | Autre commentaire |
