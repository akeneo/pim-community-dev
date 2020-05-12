Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Skip rules with missing actions key
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:  set
            field: description
            value: Another good description
            locale: en_US
            scope: tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            wrong:
                - type:  set
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            wrong:
                - type:  set
                  field: description
                  value: The new Sony description

    """
    Then an exception with message "The \"actions\" key is missing or empty" has been thrown
    And the rule list contains the rules:
    """
    sony_beautiful_description:
        priority: 10
        conditions:
            - field:    name
              operator: CONTAINS
              value:    Canon
              locale:   en_US
        actions:
            - type:  set
              field: description
              value: Another good description
              locale: en_US
              scope: tablet
    """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing from_field key for copy action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:     copy
                  to_field: description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:     copy
                  to_field: name

    """
    Then an exception with message "actions[0].fromField: The \"from_field\" key is missing or empty" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing to_field key for copy action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy
                  from_field: camera_model_name
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy
                  from_field: camera_model_name

    """
    Then an exception with message "actions[0].toField: The \"to_field\" key is missing or empty" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing value key for set action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:   set
                  field:  description
                  wrong:  A beautiful description
                  locale: en_US
                  scope:  mobile
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:   set
                  field:  description
                  wrong:  The new Sony description
                  locale: en_US
                  scope:  mobile

    """
    Then an exception with message "actions[0].value: The \"value\" key is missing or empty" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules without locale key for condition and set action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:   set
                  field:  description
                  value:  A beautiful description
                  scope:  tablet
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:   set
                  field:  description
                  value:  Another beautiful description
                  scope:  tablet
    """
    Then an exception with message "conditions[0]: Attribute \"name\" expects a locale, none given." has been thrown
    And an exception with message "actions[0]: Attribute \"description\" expects a locale, none given." has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with nonexistent locale key for condition and set action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   kj_KL
                  scope:    tablet
            actions:
                - type:   set
                  field:  description
                  value:  A beautiful description
                  locale: kj_KL
                  scope:  tablet
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   kj_KL
                  scope:    tablet
            actions:
                - type:   set
                  field:  description
                  value:  Another beautiful description
                  locale: kj_KL
                  scope:  tablet
    """
    Then an exception with message "conditions[0]: Attribute \"name\" expects an existing and activated locale, \"kj_KL\" given" has been thrown
    And an exception with message "actions[0]: Attribute \"description\" expects an existing and activated locale, \"kj_KL\" given" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules without scope key for condition and set action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:   set
                  field:  description
                  value:  A beautiful description
                  locale: en_US
        sony_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:   set
                  field:  description
                  value:  Another beautiful description
                  locale: en_US
    """
    Then an exception with message "conditions[0]: Attribute \"description\" expects a scope, none given." has been thrown
    And an exception with message "actions[0]: Attribute \"description\" expects a scope, none given" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with nonexistent scope key for condition and set action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
                  scope:    nonexistent
            actions:
                - type:   set
                  field:  description
                  value:  A beautiful description
                  locale: en_US
                  scope:  nonexistent
        sony_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
                  scope:    nonexistent
            actions:
                - type:   set
                  field:  description
                  value:  Another beautiful description
                  locale: en_US
                  scope:  nonexistent
    """
    Then an exception with message "conditions[0]: Attribute \"description\" expects an existing scope, \"nonexistent\" given." has been thrown
    And an exception with message "actions[0]: Attribute \"description\" expects an existing scope, \"nonexistent\" given." has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing action type key
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - wrong: set
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - wrong: set
                  field: description
                  value: The new Sony description

    """
    Then an exception with message "actions[0].type: The \"type\" key is missing or empty" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with invalid action type
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  wrong
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  another wrong
                  field: description
                  value: The new Sony description

    """
    Then an exception with message "actions[0].type: Unknown action type: wrong" has been thrown
    Then an exception with message "actions[0].type: Unknown action type: another wrong" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with non existing field for set action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set
                  field: wrong
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set
                  field: another wrong
                  value: The new Sony description

    """
    Then an exception with message "actions[0].field: You cannot set data to the \"wrong\" field." has been thrown
    And an exception with message "actions[0].field: You cannot set data to the \"another wrong\" field." has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with non existing from_field for copy action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy
                  from_field: wrong
                  to_field:   description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy
                  from_field: another wrong
                  to_field:   description

    """
    Then an exception with message "actions[0]: You cannot copy data from the \"wrong\" field to the \"description\" field." has been thrown
    And an exception with message "actions[0]: You cannot copy data from the \"another wrong\" field to the \"description\" field." has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with non existing to_field for copy action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy
                  from_field: description
                  to_field:   wrong
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy
                  from_field: description
                  to_field:   another wrong

    """
    Then an exception with message "actions[0]: You cannot copy data from the \"description\" field to the \"wrong\" field." has been thrown
    And an exception with message "actions[0]: You cannot copy data from the \"description\" field to the \"another wrong\" field." has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with incompatible fields for copy action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy
                  from_field: description
                  to_field:   side_view
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy
                  from_field: description
                  to_field:   side_view

    """
    Then an exception with message "actions[0]: You cannot copy data from the \"description\" field to the \"side_view\" field" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with wrong locale fields for copy action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:        copy
                  from_field:  name
                  to_field:    name
                  from_locale: wrong
                  to_locale:   wrong
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:        copy
                  from_field:  name
                  to_field:    name
                  from_locale: wrong
                  to_locale:   wrong
    """
    Then an exception with message "actions[0]: Attribute \"name\" expects an existing and activated locale, \"wrong\" given" has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with wrong scope fields for copy action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:        copy
                  from_field:  description
                  to_field:    description
                  from_locale: en_US
                  to_locale:   en_US
                  from_scope:  wrong
                  to_scope:    wrong
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:        copy
                  from_field:  description
                  to_field:    description
                  from_locale: en_US
                  to_locale:   en_US
                  from_scope:  wrong
                  to_scope:    wrong
    """
    Then an exception with message "actions[0]: Attribute \"description\" expects an existing scope, \"wrong\" given." has been thrown
    And the rule list contains the rule:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with invalid include_children option for remove action
    Given the following product rule definitions:
      """
      remove_option_weather_for_disabled_jackets:
        priority: 10
        conditions:
          - field:    family
            operator: IN
            value:
              - jackets
          - field:    enabled
            operator: =
            value:    false
        actions:
          - type:  remove
            field: weather_conditions
            items:
              - dry
      """
    When the following yaml file is imported:
    """
    rules:
        remove_option_weather_for_disabled_jackets:
            priority: 10
            conditions:
                - field:    family
                  operator: IN
                  value:
                      - jackets
                - field:    enabled
                  operator: =
                  value:    false
            actions:
              - type:  remove
                field: weather_conditions
                items:
                    - wet
                include_children: true
    """
    Then an exception with message "actions[0]: The \"include_children\" option can only be applied with field \"categories\", \"weather_conditions\" given" has been thrown
    And the rule list contains the rule:
      """
      remove_option_weather_for_disabled_jackets:
        priority: 10
        conditions:
          - field:    family
            operator: IN
            value:
              - jackets
          - field:    enabled
            operator: =
            value:    false
        actions:
          - type:  remove
            field: weather_conditions
            items:
              - dry
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with invalid include_children option type for remove action
    Given the following product rule definitions:
      """
      remove_categories_for_disabled_jackets:
        priority: 10
        conditions:
          - field:    family
            operator: IN
            value:
              - jackets
          - field:    enabled
            operator: =
            value:    false
        actions:
          - type:  remove
            field: categories
            items:
              - summer_collection
            include_children: true
      """
    When the following yaml file is imported:
    """
    rules:
        remove_categories_for_disabled_jackets:
            priority: 10
            conditions:
                - field:    family
                  operator: IN
                  value:
                  - jackets
                - field:    enabled
                  operator: =
                  value:    false
            actions:
                - type:  remove
                  field: categories
                  items:
                    - 2014_collection
                  include_children: yolo
    """
    Then an exception with message "actions[0].includeChildren: This value should be of type bool." has been thrown
    And the rule list contains the rule:
      """
      remove_categories_for_disabled_jackets:
        priority: 10
        conditions:
          - field:    family
            operator: IN
            value:
              - jackets
          - field:    enabled
            operator: =
            value:    false
        actions:
          - type:  remove
            field: categories
            items:
              - summer_collection
            include_children: true
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rule with invalid locale and scope for concatenate action
    Given the following attributes:
      | code       | label-en_US | type             | scopable | localizable | allowed_extensions | metric_family | default_metric_unit | group | decimals_allowed |
      | rear_view  | Rear view   | pim_catalog_text | 1        | 1           |                    |               |                     | other |                  |
    When the following yaml file is imported:
    """
    rules:
        bad_locale_and_scope:
            conditions:
                - field:    sku
                  operator: =
                  value:    test
            actions:
                - type: concatenate
                  from:
                      - field: sku
                        locale: en_US
                      - field: sku
                        scope: mobile
                      - field: name
                        locale: unknown
                      - field: rear_view
                  to:
                      field: description
                      scope: tablet
                      locale: en_US
    """
    Then an exception with message "actions[0].from[0]: Attribute \"sku\" does not expect a locale, \"en_US\" given" has been thrown
    And an exception with message "actions[0].from[1]: Attribute \"sku\" does not expect a scope, \"mobile\" given" has been thrown
    And an exception with message "actions[0].from[2]: Attribute \"name\" expects an existing and activated locale, \"unknown\" given" has been thrown
    And an exception with message "actions[0].from[3]: Attribute \"rear_view\" expects a scope, none given" has been thrown
    And an exception with message "actions[0].from[3]: Attribute \"rear_view\" expects a locale, none given." has been thrown
    And the rule list does not contain the "bad_locale_and_scope" rule
