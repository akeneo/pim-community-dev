@javascript
Feature: Export variant groups with localized values
  In order to be able to export with locale values
  As a product manager
  I need to be able to export variant groups with dates

  Scenario: Successfully export variant groups with date attribute
    Given a "footwear" catalog configuration
    And the following variant group values:
      | group             | attribute       | value      |
      | caterpillar_boots | destocking_date | 1999-12-28 |
    And the following job "footwear_variant_group_export" configuration:
      | filePath   | %tmp%/variant_group_export/variant_group_export.csv |
      | dateFormat | dd/MM/yyyy                                          |
    And I am logged in as "Julien"
    And I am on the "footwear_variant_group_export" export job page
    When I launch the export job
    And I wait for the "footwear_variant_group_export" job to finish
    Then I should see "lu 1"
    And I should see "écrit 1"
    And exported file of "footwear_variant_group_export" should contain:
      """
      code;axis;destocking_date;label-en_US;type
      caterpillar_boots;color,size;28/12/1999;"Caterpillar boots";VARIANT
      """
