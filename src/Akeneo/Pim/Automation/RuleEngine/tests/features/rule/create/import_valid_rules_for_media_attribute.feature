Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules with media attributes

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type media in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:        copy
                  from_field:  side_view
                  to_field:    side_view
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import valid rule for "media" attribute in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_media:
            conditions:
              - field:    side_view
                operator: =
                value:    akeneo.jpg
            actions:
                - type:  set
                  field: side_view
                  value: %fixtures%/akeneo.jpg
    """
    Then no exception has been thrown
    And the rule list contains the imported rules
