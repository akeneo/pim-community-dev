Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules with text attribute

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Import valid rule for "text" attribute in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        sony_beautiful_name:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Super Name
                  locale:   fr_FR
            actions:
                - type:  set
                  field: name
                  value: My new Super Name
                  locale: en_US
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import valid rule for "textarea" attribute in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Another good description
                  locale:   fr_FR
                  scope:    tablet
            actions:
                - type:   set
                  field:  description
                  value:  My new description
                  locale: en_US
                  scope:  tablet
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type textarea in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:        copy
                  from_field:  description
                  to_field:    description
                  from_scope:  mobile
                  to_scope:    tablet
                  from_locale: en_US
                  to_locale:   en_US
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type text and text in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:        copy
                  from_field:  name
                  to_field:    name
                  from_locale: en_US
                  to_locale:   en_US
    """
    Then no exception has been thrown
    And the rule list contains the imported rules
