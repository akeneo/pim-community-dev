Feature: Import groups
  In order to reuse the groups of my products
  As a product manager
  I need to be able to import groups

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code         | label-en_US | type  |
      | ORO_XSELL    | Oro X       | XSELL |
      | AKENEO_XSELL | Akeneo X    | XSELL |

  Scenario: Successfully import standard groups to create and update products
    Given the following CSV file to import:
      """
      code;label-en_US;type
      default;;RELATED
      ORO_XSELL;Oro X;XSELL
      AKENEO_XSELL;Akeneo XSell;XSELL
      AKENEO_NEW;US;XSELL
      """
    When I import it via the job "csv_footwear_group_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "read lines 4"
    And I should see the text "Created 2"
    And I should see the text "Processed 2"
    And I should not see "Skip"
    Then there should be the following groups:
      | code          | label-en_US    | label-fr_FR | type    | axis       |
      | ORO_XSELL     | Oro X          |             | XSELL   |            |
      | AKENEO_XSELL  | Akeneo XSell   |             | XSELL   |            |
      | AKENEO_NEW    | US             |             | XSELL   |            |
      | default       |                |             | RELATED |            |

  Scenario: Skip the line when encounter the change of a type with import
    Given the following CSV file to import:
      """
      code;label-en_US;type
      AKENEO_XSELL;;RELATED
      """
    When I import it via the job "csv_footwear_group_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "This property cannot be changed"
    And I should see the text "read lines 1"
    And I should see the text "Skipped 1"
    Then there should be the following groups:
      | code          | label-en_US    | label-fr_FR | type    | axis       |
      | ORO_XSELL     | Oro X          |             | XSELL   |            |
      | AKENEO_XSELL  | Akeneo X       |             | XSELL   |            |

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip the line when encounter an empty code
    Given the following CSV file to import:
      """
      code;label-en_US;label-en_US;type
      ;;;RELATED
      """
    When I import it via the job "csv_footwear_group_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "read lines 1"
    And I should see the text "Field \"code\" must be filled"
