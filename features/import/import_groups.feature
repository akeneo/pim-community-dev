@javascript
Feature: Import groups
  In order to reuse the groups of my products
  As Julia
  I need to be able to import groups

  Scenario: Successfully import groups
    Given the "default" catalog configuration
    And the following jobs:
      | connector            | alias            | code              | label                 | type   |
      | Akeneo CSV Connector | csv_group_import | acme_group_import | Group import for Acme | import |
    And I am logged in as "Julia"
    And the following attributes:
      | code  | label | type         |
      | color | Color | simpleselect |
      | size  | Size  | simpleselect |
    And the following group types:
      | code    |
      | RELATED |
    And the following product groups:
      | code           | label       | type    | attributes |
      | ORO_TSHIRT     | Oro T-shirt | VARIANT | size       |
      | AKENEO_VARIANT | Akeneo      | VARIANT | size       |
    And the following file to import:
    """
    code;label-en_US;label-fr_FR;type;attributes
    default;;;RELATED;
    AKENEO_MUG;Akeneo Mug;Tasse Akeneo;VARIANT;color
    AKENEO_TSHIRT;Akeneo T-Shirt;T-Shirt Akeneo;VARIANT;color,size
    ORO_TSHIRT;Pouet;Pouic;VARIANT;size,color
    AKENEO_VARIANT;;;VARIANT;size
    """
    And the following job "acme_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "acme_group_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be the following groups:
      | code           | label-en_US    | label-fr_FR    | type    | attributes |
      | default        |                |                | RELATED |            |
      | AKENEO_MUG     | Akeneo Mug     | Tasse Akeneo   | VARIANT | color      |
      | AKENEO_TSHIRT  | Akeneo T-Shirt | T-Shirt Akeneo | VARIANT | color,size |
      | ORO_TSHIRT     | Oro T-shirt    | Pouic          | VARIANT | color,size |
      | AKENEO_VARIANT | Akeneo         |                | VARIANT | size       |

  Scenario: Fail to change group type with import
    Given the "default" catalog configuration
    And the following jobs:
      | connector            | alias            | code              | label                 | type   |
      | Akeneo CSV Connector | csv_group_import | acme_group_import | Group import for Acme | import |
    And I am logged in as "Julia"
    And the following attributes:
      | code  | label | type         |
      | color | Color | simpleselect |
      | size  | Size  | simpleselect |
    And the following group types:
      | code    |
      | RELATED |
    And the following product groups:
      | code           | label       | type    | attributes |
      | ORO_TSHIRT     | Oro T-shirt | VARIANT | size       |
      | AKENEO_VARIANT | Akeneo      | VARIANT | size       |
    And the following file to import:
    """
    code;label-en_US;label-fr_FR;type;attributes
    AKENEO_VARIANT;;;RELATED;
    """
    And the following job "acme_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "acme_group_import" import job page
    And I launch the import job
    And I wait for the job to finish
    And I should see "This property cannot be changed"
