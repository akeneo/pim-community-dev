Feature: Export variant groups
  In order to be able to access and modify groups data outside PIM
  As a product manager
  I need to be able to export variant groups

  @javascript
  Scenario: Successfully export groups
    Given a "footwear" catalog configuration
    And the following job "footwear_variant_group_export" configuration:
      | filePath | %tmp%/variant_group_export/variant_group_export.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_variant_group_export" export job page
    When I launch the export job
    And I wait for the "footwear_variant_group_export" job to finish
    Then I should see "Read 1"
    And I should see "Written 1"
    And exported file of "footwear_variant_group_export" should contain:
    """
    code;type;attributes;label-en_US
    caterpillar_boots;VARIANT;color,size;"Caterpillar boots"
    """