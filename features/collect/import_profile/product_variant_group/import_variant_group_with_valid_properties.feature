@javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to import variant group to create or update them

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code   | label  | axis        | type    |
      | SANDAL | Sandal | color, size | VARIANT |
      | NOT_VG | Not VG |             | RELATED |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of variant group to create a new one
    Given the following CSV file to import:
      """
      code;type;axis;label-en_US
      NEW_ONE;VARIANT;size,color;"My new VG 1"
      NEW_TWO;VARIANT;color;"My new VG 2"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "read lines 2"
    And I should see "Created 2"
    And there should be the following groups:
      | code    | label-en_US | label-fr_FR | axis       | type    |
      | SANDAL  | Sandal      |             | color,size | VARIANT |
      | NOT_VG  | Not VG      |             |            | RELATED |
      | NEW_ONE | My new VG 1 |             | color,size | VARIANT |
      | NEW_TWO | My new VG 2 |             | color      | VARIANT |

  Scenario: Successfully import a csv file of variant group to update an existing one
    Given the following CSV file to import:
      """
      code;type;axis;label-en_US
      SANDAL;VARIANT;color,size;"My new label"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "read lines 1"
    And I should see "Processed 1"
    And there should be the following groups:
      | code   | label-en_US  | label-fr_FR | axis       | type    |
      | SANDAL | My new label |             | color,size | VARIANT |
      | NOT_VG | Not VG       |             |            | RELATED |
