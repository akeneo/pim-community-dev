@javascript
Feature: Execute an import with scopable or localizable data
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

  Background:
    Given the "apparel" catalog configuration
    And the following product groups:
      | code   | label  | axis        | type    |
      | SANDAL | Sandal | size, color | VARIANT |
    And I am logged in as "Julia"

  Scenario: Avoid data loss when importing existing variant group with localizable/scopable attributes
    Given I am on the "SANDAL" variant group page
    And I visit the "Attributes" tab
    And I add available attributes Description
    And I expand the "Description" attribute
    And I fill in the following information:
      | tablet Description | original description tablet |
      | print Description  | original description print  |
    And I save the variant group
    And the following CSV file to import:
      """
      code;type;label-en_US;axis;description-en_US-tablet
      SANDAL;VARIANT;Sandal;color,size;"new description tablet"
      """
    And the following job "variant_group_import" configuration:
      | filePath | %file to import% |
    And I am on the "variant_group_import" import job page
    And I launch the import job
    And I wait for the "variant_group_import" job to finish
    And I am on the "SANDAL" variant group page
    And I visit the "Attributes" tab
    And I expand the "Description" attribute
    Then the field tablet Description should contain "new description tablet"
    And the field print Description should contain "original description print"

  Scenario: Have coherent values when impJorting new variant group with localizable/scopable attributes
    Given the following attributes:
      | code             | label-en_US      | label-fr_FR      | label-de_DE        | type   | localizable | scopable | group   | metric_family | default_metric_unit |
      | sole_length      | Sole length      | Longueur semelle | Einlegesohlenlänge | metric | yes         | no       | general | Length        | CENTIMETER          |
      | packaging_weight | Packaging weight | Poids packaging  | Verpackungsgewicht | metric | no          | yes      | general | Weight        | KILOGRAM            |
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
    Then the field tablet Description should contain "just a description for the tablet"
    And the field print Description should contain ""
    And the field tablet Packaging weight should contain "1230"
    And the field print Packaging weight should contain ""
    And the field Sole length should contain "10"
    When I switch the locale to "de_DE"
    Then the field ecommerce Beschreibung should contain ""
    And the field print Beschreibung should contain ""
