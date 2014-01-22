@javascript
Feature: Import options
  In order to reuse the options
  As Julia
  I need to be able to import options

  Scenario: Successfully import options
    Given the "default" catalog configuration
    And the following attributes:
      | code         | label | type         |
      | manufacturer | Man   | simpleselect |
    And the following jobs:
      | connector            | alias                       | code               | label                  | type   |
      | Akeneo CSV Connector | csv_attribute_option_import | acme_option_import | Option import for Acme | import |
    And I am logged in as "Julia"
    And the following file to import:
    """
    attribute;code;default;label-en_US
    manufacturer;Converse;0;Converse
    manufacturer;TimberLand;0;TimberLand
    manufacturer;Nike;0;Nike
    manufacturer;Caterpillar;1;Caterpillar
    """
    And the following job "acme_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "acme_option_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be the following options:
      | attribute    | code        | default | label-en_US |
      | manufacturer | Converse    | 0       | Converse    |
      | manufacturer | TimberLand  | 0       | TimberLand  |
      | manufacturer | Nike        | 0       | Nike        |
      | manufacturer | Caterpillar | 1       | Caterpillar |

