@javascript
Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Skip rules with missing actions key
    And the following product rule definitions:
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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "Rule content \"canon_beautiful_description\" should have a \"actions\" key."
    And I should see the text "Rule content \"sony_beautiful_description\" should have a \"actions\" key."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the text "sony_beautiful_description"
    And I should see the text "Another good description"

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0].fromField: The key \"from_field\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then the row "sony_beautiful_description" should contain the texts:
      | column | value                                                                         |
      | Action | Then description [ en \| mobile ] is copied into description [ en \| tablet ] |

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0].toField: The key \"to_field\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then the row "sony_beautiful_description" should contain the texts:
      | column | value                                                                         |
      | Action | Then description [ en \| mobile ] is copied into description [ en \| tablet ] |

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0].value: The key \"value\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see the text "Another good description"

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see the text "skipped 2"
    And I should see the text "conditions[0]: Attribute \"name\" expects a locale, none given."
    And I should see the text "actions[0]: Attribute \"description\" expects a locale, none given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see the text "Another good description"

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see the text "skipped 2"
    And I should see the text "conditions[0]: Attribute \"name\" expects an existing and activated locale, \"kj_KL\" given."
    And I should see the text "actions[0]: Attribute \"description\" expects an existing and activated locale, \"kj_KL\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see the text "Another good description"

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see the text "skipped 2"
    And I should see the text "conditions[0]: Attribute \"description\" expects a scope, none given."
    And I should see the text "actions[0]: Attribute \"description\" expects a scope, none given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see the text "Another good description"

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see the text "skipped 2"
    And I should see the text "conditions[0]: Attribute \"description\" expects an existing scope, \"nonexistent\" given."
    And I should see the text "actions[0]: Attribute \"description\" expects an existing scope, \"nonexistent\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see the text "Another good description"

  Scenario: Skip rules with missing type key for copy or set action
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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "Rule content \"{\"conditions\":[{\"field\":\"name\",\"operator\":\"CONTAINS\",\"value\":\"Canon\",\"locale\":\"en_US\"}],\"actions\":[{\"wrong\":\"set\",\"field\":\"description\",\"value\":\"A beautiful description\"}]}\" has an action with no type."
    And I should see the text "Rule content \"{\"conditions\":[{\"field\":\"name\",\"operator\":\"CONTAINS\",\"value\":\"Canon\"}],\"actions\":[{\"wrong\":\"set\",\"field\":\"description\",\"value\":\"The new Sony description\"}]}\" has an action with no type."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see the text "Another good description"

  Scenario: Skip rules with invalid type for copy or set action
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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "Rule content \"{\"conditions\":[{\"field\":\"name\",\"operator\":\"CONTAINS\",\"value\":\"Canon\",\"locale\":\"en_US\"}],\"actions\":[{\"type\":\"wrong\",\"field\":\"description\",\"value\":\"A beautiful description\"}]}\" has an unknown type of action \"wrong\"."
    And I should see the text "Rule content \"{\"conditions\":[{\"field\":\"name\",\"operator\":\"CONTAINS\",\"value\":\"Canon\",\"locale\":\"en_US\"}],\"actions\":[{\"type\":\"another wrong\",\"field\":\"description\",\"value\":\"The new Sony description\"}]}\" has an unknown type of action \"another wrong\"."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see the text "Another good description"

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0].field: You cannot set data to the \"wrong\" field."
    And I should see the text "actions[0].field: You cannot set data to the \"another wrong\" field."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see the text "Another good description"

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0]: You cannot copy data from \"wrong\" field to the \"description\" field."
    And I should see the text "actions[0]: You cannot copy data from \"another wrong\" field to the \"description\" field."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then the row "sony_beautiful_description" should contain the texts:
      | column | value                                                                         |
      | Action | Then description [ en \| mobile ] is copied into description [ en \| tablet ] |

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0]: You cannot copy data from \"description\" field to the \"wrong\" field."
    And I should see the text "actions[0]: You cannot copy data from \"description\" field to the \"another wrong\" field."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then the row "sony_beautiful_description" should contain the texts:
      | column | value                                                                         |
      | Action | Then description [ en \| mobile ] is copied into description [ en \| tablet ] |

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0]: No copier found for fields \"description\" and \"side_view\""
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then the row "sony_beautiful_description" should contain the texts:
      | column | value                                                                         |
      | Action | Then description [ en \| mobile ] is copied into description [ en \| tablet ] |

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0]: Attribute \"name\" expects an existing and activated locale, \"wrong\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then the row "sony_beautiful_description" should contain the texts:
      | column | value                                                                         |
      | Action | Then description [ en \| mobile ] is copied into description [ en \| tablet ] |

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
    And the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "actions[0]: Attribute \"description\" expects an existing scope, \"wrong\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then the row "sony_beautiful_description" should contain the texts:
      | column | value                                                                         |
      | Action | Then description [ en \| mobile ] is copied into description [ en \| tablet ] |

  Scenario: Skip rules with invalid include_children option for remove action
    Given the following product rule definitions:
      """
      sony_beautiful_description:
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
    And the following yaml file to import:
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
              - type:           remove
                field:          weather_conditions
                items:
                    - wet
                include_children: true
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 1"
    And I should see the text "actions[0]: The \"include_children\" option can only be applied with field \"categories\", \"weather_conditions\" given"
    When I am on the "weather_conditions" attribute page
    And I visit the "Rules" tab
    Then the row "sony_beautiful_description" should contain the texts:
      | column | value                                       |
      | Action | Then dry is removed from weather_conditions |
