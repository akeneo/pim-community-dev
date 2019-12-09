@javascript
Feature: Import Xlsx groups
  In order to reuse the groups of my products
  As a product manager
  I need to be able to import groups with XLSX files

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code         | label-en_US | type  |
      | ORO_XSELL    | Oro X       | XSELL |
      | AKENEO_XSELL | Akeneo X    | XSELL |
    And I am logged in as "Julia"

  Scenario: Successfully import standard groups to create and update products
    Given the following XLSX file to import:
      """
      code;label-en_US;type
      default;;RELATED
      ORO_XSELL;Oro X;XSELL
      AKENEO_XSELL;Akeneo XSell;XSELL
      AKENEO_NEW;US;XSELL
      """
    And the following job "xlsx_footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_group_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_group_import" job to finish
    Then I should see the text "read lines 4"
    And I should see the text "created 2"
    And I should see the text "processed 2"
    Then there should be the following groups:
      | code          | label-en_US    | label-fr_FR | type    | axis       |
      | ORO_XSELL     | Oro X          |             | XSELL   |            |
      | AKENEO_XSELL  | Akeneo XSell   |             | XSELL   |            |
      | AKENEO_NEW    | US             |             | XSELL   |            |
      | default       |                |             | RELATED |            |
