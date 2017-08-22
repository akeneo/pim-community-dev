@javascript
Feature: Execute an import with scopable or localizable data
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

  Background:
    Given the "apparel" catalog configuration
    And the following variant groups:
      | code   | label-en_US | axis       | type    |
      | SANDAL | Sandal      | size,color | VARIANT |
    And I am logged in as "Julia"

  @skip @info Will be removed in PIM-6444
  Scenario: Avoid data loss when importing variant group localizable/scopable values
    Given I am on the "SANDAL" variant group page
    And I visit the "Attributes" tab
    And I add available attributes Description
    And I change the Description for scope tablet and locale en_US to "original description tablet"
    And I change the Description for scope print and locale en_US to "original description print"
    And I save the variant group
    And the following CSV file to import:
      """
        label-en_US;axis;code;description-en_US-tablet;type
        Sandal;color,size;SANDAL;"new description tablet";VARIANT
      """
    And the following job "variant_group_import" configuration:
      | filePath | %file to import% |
    And I am on the "variant_group_import" import job page
    And I launch the import job
    And I wait for the "variant_group_import" job to finish
    And I am on the "SANDAL" variant group page
    And I visit the "Attributes" tab
    Then the field Description for locale "en_US" and scope "tablet" should contain "new description tablet"
    And the field Description for locale "en_US" and scope "print" should contain "original description print"

  @skip @info Will be removed in PIM-6444
  Scenario: Have coherent values when importing new variant group with localizable/scopable attributes
    Given the following attributes:
      | code             | label-en_US      | label-fr_FR      | type               | localizable | scopable | group   | metric_family | default_metric_unit | decimals_allowed | negative_allowed |
      | sole_length      | Sole length      | Longueur semelle | pim_catalog_metric | 1           | 0        | general | Length        | CENTIMETER          |                0 |                0 |
      | packaging_weight | Packaging weight | Poids packaging  | pim_catalog_metric | 0           | 1        | general | Weight        | KILOGRAM            |                0 |                0 |
    And the following CSV file to import:
      """
      code;type;label-en_US;axis;description-en_US-tablet;sole_length-en_US;packaging_weight-tablet
      HIGH_HEEL;VARIANT;High heel;color,size;"just a description for the tablet";"10 INCH";"1230 GRAM"
      """
    And the following job "variant_group_import" configuration:
      | filePath | %file to import% |
    And I am on the "variant_group_import" import job page
    And I launch the import job
    And I wait for the "variant_group_import" job to finish
    And I am on the "HIGH_HEEL" variant group page
    And I visit the "Attributes" tab
    And I expand the "Description" attribute
    And I switch the scope to "tablet"
    Then the field Description should contain "just a description for the tablet"
    And I switch the scope to "print"
    And the field Description should contain ""
    And I switch the scope to "tablet"
    And the field Packaging weight should contain "1230"
    And I switch the scope to "print"
    And the field Packaging weight should contain ""
    And the field Sole length should contain "10"
    When I switch the locale to "fr_FR"
    And I switch the scope to "tablet"
    Then I should see the text "This localizable field is not available for locale 'fr_FR' and channel 'tablet'"
