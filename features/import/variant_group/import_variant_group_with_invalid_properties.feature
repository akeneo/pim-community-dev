@javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to be notified when I use not valid groups (not know or not variant group)

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code   | label  | attributes  | type    |
      | SANDAL | Sandal | size, color | VARIANT |
      | NOT_VG | Not VG | color, size | RELATED |
    And I am logged in as "Julia"

  Scenario: Stop the import if variant group code column is not provided
    Given the following CSV file to import:
      """
      name-en_US;axis;description-en_US-tablet;color
      My sandal;color;My sandal description for locale en_US and channel tablet;white
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "No identifier column"

  Scenario: Fail to import variant group with updated axis (here we try to change color and size to color)
    Given the following CSV file to import:
    """
    code;axis;label-en_US
    SANDAL;size;"Sandal"
    """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Read 1"
    And I should see "Updated 1"
    And there should be the following groups:
      | code    | label-en_US | label-fr_FR | attributes | type    |
      | SANDAL  | Sandal      |             | color,size | VARIANT |
      | NOT_VG  | Not VG      |             |            | RELATED |

  Scenario: Stop the import when encounter a new variant group with no axis
    Given the following CSV file to import:
    """
    code;axis;label-en_US
    NO_AXIS;;"My VG with no axis"
    """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Status: Failed"
    And I should see "Axis must be provided for the new variant group \"NO_AXIS\""
    And I should see "Read 1"
    And there should be the following groups:
      | code    | label-en_US | label-fr_FR | attributes | type    |
      | SANDAL  | Sandal      |             | color,size | VARIANT |
      | NOT_VG  | Not VG      |             |            | RELATED |

  Scenario: Skip the line when encounter an existing group which is not a variant group
    Given the following CSV file to import:
    """
    code;axis;label-en_US
    NOT_VG;;"My standard not updatable group"
    """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Variant group \"NOT_VG\" does not exist"
    And I should see "Read 1"
    And I should see "Skipped 1"
    And there should be the following groups:
      | code    | label-en_US | label-fr_FR | attributes | type    |
      | SANDAL  | Sandal      |             | color,size | VARIANT |
      | NOT_VG  | Not VG      |             |            | RELATED |