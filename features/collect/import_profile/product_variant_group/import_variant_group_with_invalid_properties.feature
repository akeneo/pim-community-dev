@javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to be notified when I use not valid groups (not know or not variant group)

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code   | label  | axis        | type    |
      | SANDAL | Sandal | size, color | VARIANT |
      | NOT_VG | Not VG |             | RELATED |
    And I am logged in as "Julia"

  Scenario: Stop the import if variant group code column is not provided
    Given the following CSV file to import:
      """
      type;name-en_US;axis;description-en_US-tablet;color
      VARIANT;My sandal;color;My sandal description for locale en_US and channel tablet;white
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Status: FAILED"
    And I should see:
    """
    Field "code" is expected, provided fields are "type, name-en_US, axis, description-en_US-tablet, color"
    """

  Scenario: Skip the line when encounter a line with updated axis (here we try to replace the axis color by manufacturer)
    Given the following CSV file to import:
      """
      code;type;axis;label-en_US
      SANDAL;VARIANT;manufacturer,size;"Sandal"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Attributes: This property cannot be changed."
    And I should see "read lines 1"
    And I should see "Skipped 1"
    And there should be the following groups:
      | code   | label-en_US | label-fr_FR | axis       | type    |
      | SANDAL | Sandal      |             | color,size | VARIANT |
      | NOT_VG | Not VG      |             |            | RELATED |

  Scenario: Skip the line when encounter a line with updated axis (here we try to remove the axis size)
    Given the following CSV file to import:
      """
      code;type;axis;label-en_US
      SANDAL;VARIANT;color;"Sandal"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Attributes: This property cannot be changed."
    And I should see "read lines 1"
    And I should see "Skipped 1"
    And there should be the following groups:
      | code   | label-en_US | label-fr_FR | axis       | type    |
      | SANDAL | Sandal      |             | color,size | VARIANT |
      | NOT_VG | Not VG      |             |            | RELATED |

  Scenario: Skip the line when encounter a new variant group with no axis
    Given the following CSV file to import:
      """
      code;type;axis;label-en_US
      NO_AXIS;VARIANT;;"My VG with no axis"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Variant group \"NO_AXIS\" must be defined with at least one axis"
    And I should see "read lines 1"
    And I should see "Skipped 1"
    And there should be the following groups:
      | code   | label-en_US | label-fr_FR | axis       | type    |
      | SANDAL | Sandal      |             | color,size | VARIANT |
      | NOT_VG | Not VG      |             |            | RELATED |

  Scenario: Skip the line when encounter an existing group which is not a variant group
    Given the following CSV file to import:
      """
      code;type;axis;label-en_US
      NOT_VG;VARIANT;;"My standard not updatable group"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Cannot process group \"NOT_VG\", only variant groups are accepted"
    And I should see "read lines 1"
    And I should see "Skipped 1"
    And there should be the following groups:
      | code   | label-en_US | label-fr_FR | axis       | type    |
      | SANDAL | Sandal      |             | color,size | VARIANT |
      | NOT_VG | Not VG      |             |            | RELATED |
