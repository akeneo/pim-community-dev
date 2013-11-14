Feature: Export associations
  In order to be able to access and modify associations data outside PIM
  As Julia
  I need to be able to export associations

  @javascript
  Scenario: Successfully export associations
    Given the following jobs:
      | connector            | alias              | code                    | label                       | type   |
      | Akeneo CSV Connector | association_export | acme_association_export | Association export for Acme | export |
    And I am logged in as "Julia"
    And the following associations:
      | code   | label      |
      | UPSELL | Upsell     |
      | X_SELL | Cross sell |
    And the following job "acme_association_export" configuration:
      | element | property      | value               |
      | writer  | directoryName | /tmp/               |
      | writer  | fileName      | association_export.csv |
    And I am on the "acme_association_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then file "/tmp/association_export.csv" should contain 3 rows
