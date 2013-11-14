Feature: Export groups
  In order to be able to access and modify groups data outside PIM
  As Julia
  I need to be able to export groups

  @javascript
  Scenario: Successfully export groups
    Given the following jobs:
      | connector            | alias        | code              | label                 | type   |
      | Akeneo CSV Connector | group_export | acme_group_export | Group export for Acme | export |
    And I am logged in as "Julia"
    And the following product groups:
      | code           | label       | type    | attributes |
      | ORO_TSHIRT     | Oro T-shirt | VARIANT | size       |
      | AKENEO_VARIANT | Akeneo      | VARIANT | size       |
    And the following job "acme_group_export" configuration:
      | element | property      | value               |
      | writer  | directoryName | /tmp/               |
      | writer  | fileName      | group_export.csv |
    And I am on the "acme_group_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then file "/tmp/group_export.csv" should contain 3 rows
