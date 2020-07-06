Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Skip rules with missing actions key
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
    """
    Then an exception with message "The \"actions\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing from_field key for copy action
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
                  to_scope: tablet
                  to_locale: en_US
    """
    Then an exception with message "actions[0].fromField: The \"from_field\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing to_field key for copy action
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
    """
    Then an exception with message "actions[0].toField: The \"to_field\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing value key for set action
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
    """
    Then an exception with message "actions[0].value: The \"value\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules without locale key for condition and set action
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
    """
    Then an exception with message "conditions[0]: The \"name\" attribute requires a locale" has been thrown
    And an exception with message "actions[0]: The \"description\" attribute requires a locale" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with nonexistent locale key for condition and set action
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   kj_KL
            actions:
                - type:   set
                  field:  description
                  value:  A beautiful description
                  locale: kj_KL
                  scope:  tablet
    """
    Then an exception with message "conditions[0]: The \"name\" attribute requires an existing and activated locale, please make sure your locale exists and is activated" has been thrown
    And an exception with message "actions[0]: The \"description\" attribute requires an existing and activated locale, please make sure your locale exists and is activated" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules without scope key for condition and set action
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
    """
    Then an exception with message "conditions[0]: The \"description\" attribute requires a scope" has been thrown
    And an exception with message "actions[0]: The \"description\" attribute requires a scope" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with nonexistent scope key for condition and set action
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
    """
    Then an exception with message "conditions[0]: The \"description\" attribute requires an existing scope" has been thrown
    And an exception with message "actions[0]: The \"description\" attribute requires an existing scope" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing action type key
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
    """
    Then an exception with message "actions[0].type: The \"type\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with invalid action type
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
    """
    Then an exception with message "actions[0].type: Unknown action type: wrong" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with non existing field for set action
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
    """
    Then an exception with message "actions[0].field: You cannot set data to the \"wrong\" field" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with non existing from_field for copy action
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
                  to_locale: en_US
                  to_scope: tablet
    """
    Then an exception with message "actions[0]: You cannot copy data from the \"wrong\" field to the \"description\" field" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with non existing to_field for copy action
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
                  from_locale: en_US
                  from_scope: tablet
                  to_field:   wrong
    """
    Then an exception with message "actions[0]: You cannot copy data from the \"description\" field to the \"wrong\" field" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with incompatible fields for copy action
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
                  from_locale: en_US
                  from_scope: tablet
                  to_field:   side_view
    """
    Then an exception with message "actions[0]: You cannot copy data from the \"description\" field to the \"side_view\" field" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with wrong locale fields for copy action
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
                  to_field:    description
                  from_locale: wrong
                  to_locale:   wrong
                  to_scope: tablet
    """
    Then an exception with message "actions[0]: The \"name\" attribute requires an existing and activated locale, please make sure your locale exists and is activated" has been thrown
    And an exception with message "actions[0]: The \"description\" attribute requires an existing and activated locale, please make sure your locale exists and is activated" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with wrong scope fields for copy action
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
                  from_field:  number_in_stock
                  to_field:    description
                  to_locale:   en_US
                  from_scope:  wrong
                  to_scope:    wrong
    """
    Then an exception with message "actions[0]: The \"number_in_stock\" attribute requires an existing scope" has been thrown
    And an exception with message "actions[0]: The \"description\" attribute requires an existing scope" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with invalid include_children option for remove action
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
    Then an exception with message "actions[0]: The \"include_children\" option can only be applied with the \"categories\" field" has been thrown
    And the rule list does not contain the "remove_option_weather_for_disabled_jackets" rule

  @integration-back
  Scenario: Skip rules with invalid include_children option type for remove action
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
    Then an exception with message "actions[0].includeChildren: This value should be of type boolean" has been thrown
    And the rule list does not contain the "remove_categories_for_disabled_jackets" rule

  @integration-back
  Scenario: Skip rule with invalid locale and scope for concatenate action
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
                      - field: neck_fabric
                  to:
                      field: description
    """
    Then an exception with message "actions[0].from[0]: The \"sku\" attribute does not require a locale, please remove it" has been thrown
    And an exception with message "actions[0].from[1]: The \"sku\" attribute does not require a scope, please remove it" has been thrown
    And an exception with message "actions[0].from[2]: The \"name\" attribute requires an existing and activated locale, please make sure your locale exists and is activated" has been thrown
    And an exception with message "actions[0].from[3]: The \"neck_fabric\" attribute requires a scope" has been thrown
    And an exception with message "actions[0].from[3]: The \"neck_fabric\" attribute requires a locale" has been thrown
    And the rule list does not contain the "bad_locale_and_scope" rule
