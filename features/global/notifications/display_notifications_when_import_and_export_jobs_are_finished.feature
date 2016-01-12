@javascript
Feature: Display notifications for import and export jobs
  In order to know when the import or export jobs I launched have finished
  As a product manager
  I need to see notifications for completed import and export jobs

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully display a notification when a job is finished
    Given I am on the "attribute_export" export job page
    And I launch the export job
    And I wait for the "attribute_export" job to finish
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                          |
      | success | Export Attribute export finished |

  Scenario: Successfully display a notification when an job finishes with errors
    Given the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit
      pim_catalog_simpleselect;lace_color;"New lace color";colors;0;1;0;0;;;
      pim_catalog_metric;length;"New length";info;0;0;0;0;;Length;INVALID_LENGTH

      """
    And the following job "attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "attribute_import" import job page
    And I launch the import job
    And I wait for the "attribute_import" job to finish
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                             |
      | warning | Import Attribute import finished with some warnings |

  Scenario: Successfully display a notification when an job fails
    Given the following CSV file to import:
    """
      foo,bar
      baz,qux
    """
    And the following job "product_import" configuration:
      | filePath | %file to import% |
    When I am on the "product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                      |
      | error | Import Product import failed |
