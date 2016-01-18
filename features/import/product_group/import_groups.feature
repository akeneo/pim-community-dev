@javascript
Feature: Import groups
  In order to reuse the groups of my products
  As a product manager
  I need to be able to import groups

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code          | label          | type    | axis        |
      | ORO_TSHIRT    | Oro T-shirt    | VARIANT | size, color |
      | AKENEO_TSHIRT | Akeneo T-shirt | VARIANT | size        |
      | ORO_XSELL     | Oro X          | XSELL   |             |
      | AKENEO_XSELL  | Akeneo X       | XSELL   |             |
    And I am logged in as "Julia"

  Scenario: Successfully import standard groups to create and update products (no variant groups)
    Given the following CSV file to import:
      """
      code;label-en_US;type
      default;;RELATED
      ORO_XSELL;Oro X;XSELL
      AKENEO_XSELL;Akeneo XSell;XSELL
      AKENEO_NEW;US;XSELL
      """
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    Then I should see "read lines 4"
    And I should see "Created 2"
    And I should see "Processed 2"
    And I should not see "Skip"
    Then there should be the following groups:
      | code          | label-en_US    | label-fr_FR | type    | axis       |
      | ORO_TSHIRT    | Oro T-shirt    |             | VARIANT | color,size |
      | AKENEO_TSHIRT | Akeneo T-shirt |             | VARIANT | size       |
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
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    Then I should see "This property cannot be changed"
    And I should see "read lines 1"
    And I should see "Skipped 1"
    Then there should be the following groups:
      | code          | label-en_US    | label-fr_FR | type    | axis       |
      | ORO_TSHIRT    | Oro T-shirt    |             | VARIANT | color,size |
      | AKENEO_TSHIRT | Akeneo T-shirt |             | VARIANT | size       |
      | ORO_XSELL     | Oro X          |             | XSELL   |            |
      | AKENEO_XSELL  | Akeneo X       |             | XSELL   |            |

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip the line when encounter an empty code
    Given the following CSV file to import:
      """
      code;label-en_US;label-en_US;type
      ;;;RELATED
      """
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    Then I should see "read lines 1"
    And I should see "Field \"code\" must be filled"

  Scenario: Skip the line if we encounter a new variant group
    Given the following CSV file to import:
      """
      code;label-en_US;type
      New_VG;Akeneo VG;VARIANT
      """
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    Then I should see "read lines 1"
    And I should see "skipped 1"
    And I should see "Cannot process variant group \"New_VG\", only groups are accepted"

  Scenario: Skip the line if we encounter an existing variant group
    Given the following CSV file to import:
      """
      code;label-en_US;type
      AKENEO_TSHIRT;Akeneo T-Shirt;VARIANT
      """
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    Then I should see "read lines 1"
    And I should see "skipped 1"
    And I should see "Cannot process variant group \"AKENEO_TSHIRT\", only groups are accepted"

  Scenario: Skip the line if we try to set axis on a standard group
    Given the following CSV file to import:
      """
      code;label-en_US;label-en_US;type;axis
      STANDARD_WITH_AXIS;;;RELATED;size
      """
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    Then I should see "read lines 1"
    And I should see "Field \"axis\" is provided, authorized fields are: \"type, code, label-en_US\""
