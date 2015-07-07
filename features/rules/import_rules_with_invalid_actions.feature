@javascript
Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Skip rules with missing actions key
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
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
                - type:  set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            wrong:
                - type:  set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "Rule content \"canon_beautiful_description\" should have a \"actions\" key."
    And I should see "Rule content \"sony_beautiful_description\" should have a \"actions\" key."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "sony_beautiful_description"
    And I should see "Another good description"

  Scenario: Skip rules with missing from_field key for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
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
                - type:     copy_value
                  to_field: description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:     copy_value
                  to_field: name

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].fromField: The key \"from_field\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with missing to_field key for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
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
                - type:       copy_value
                  from_field: camera_model_name
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:       copy_value
                  from_field: camera_model_name

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].toField: The key \"to_field\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with missing value key for set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
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
                - type:  set_value
                  field: description
                  wrong: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:  set_value
                  field: description
                  wrong: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].value: The key \"value\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules without locale key for condition and set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:   set_value
                  field:  description
                  value:  A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:   set_value
                  field:  description
                  value:  Another beautiful description
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"name\" excepts valid data, scope and locale (for filter string). Attribute \"name\" expects a locale, none given."
    And I should see "actions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for setter text). Attribute \"description\" expects a locale, none given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with nonexistent locale key for condition and set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
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
                - type:   set_value
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
                - type:   set_value
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
    And I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"name\" excepts valid data, scope and locale (for filter string). Attribute \"name\" expects an existing and activated locale, \"kj_KL\" given."
    And I should see "actions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for setter text). Attribute \"description\" expects an existing and activated locale, \"kj_KL\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules without scope key for condition and set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
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
                - type:   set_value
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
                - type:   set_value
                  field:  description
                  value:  Another beautiful description
                  locale: en_US
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for filter string). Attribute \"description\" expects a scope, none given."
    And I should see "actions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for setter text). Attribute \"description\" expects a scope, none given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with nonexistent scope key for condition and set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
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
                - type:   set_value
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
                - type:   set_value
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
    And I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for filter string). Attribute \"description\" expects an existing scope, \"nonexistent\" given."
    And I should see "actions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for setter text). Attribute \"description\" expects an existing scope, \"nonexistent\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with missing type key for copy or set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
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
                - wrong: set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - wrong: set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "Rule content \"{\"conditions\":[{\"field\":\"name\",\"operator\":\"CONTAINS\",\"value\":\"Canon\",\"locale\":\"en_US\"}],\"actions\":[{\"wrong\":\"set_value\",\"field\":\"description\",\"value\":\"A beautiful description\"}]}\" has an action with no type."
    And I should see "Rule content \"{\"conditions\":[{\"field\":\"name\",\"operator\":\"CONTAINS\",\"value\":\"Canon\"}],\"actions\":[{\"wrong\":\"set_value\",\"field\":\"description\",\"value\":\"The new Sony description\"}]}\" has an action with no type."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with invalid type for copy or set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
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
    Then I should see "skipped 2"
    And I should see "Rule content \"{\"conditions\":[{\"field\":\"name\",\"operator\":\"CONTAINS\",\"value\":\"Canon\",\"locale\":\"en_US\"}],\"actions\":[{\"type\":\"wrong\",\"field\":\"description\",\"value\":\"A beautiful description\"}]}\" has an unknown type of action \"wrong\"."
    And I should see "Rule content \"{\"conditions\":[{\"field\":\"name\",\"operator\":\"CONTAINS\",\"value\":\"Canon\",\"locale\":\"en_US\"}],\"actions\":[{\"type\":\"another wrong\",\"field\":\"description\",\"value\":\"The new Sony description\"}]}\" has an unknown type of action \"another wrong\"."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with non existing field for set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
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
                - type:  set_value
                  field: wrong
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set_value
                  field: another wrong
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].field: The field \"wrong\" does not exist."
    And I should see "actions[0].field: The field \"another wrong\" does not exist."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with non existing from_field for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
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
                - type:       copy_value
                  from_field: wrong
                  to_field:   description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: another wrong
                  to_field:   description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].fromField: The field \"wrong\" does not exist."
    And I should see "actions[0].fromField: The field \"another wrong\" does not exist."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with non existing to_field for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
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
                - type:       copy_value
                  from_field: description
                  to_field:   wrong
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: description
                  to_field:   another wrong

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].toField: The field \"wrong\" does not exist."
    And I should see "actions[0].toField: The field \"another wrong\" does not exist."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with incompatible fields for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
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
                - type:       copy_value
                  from_field: description
                  to_field:   side_view
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: description
                  to_field:   side_view

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0]: No copier found for fields \"description\" and \"side_view\""
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with wrong locale fields for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
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
                - type:        copy_value
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
                - type:        copy_value
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
    Then I should see "skipped 2"
    And I should see "actions[0]: Attribute or field \"name\" excepts valid data, scope and locale (for copier base). Attribute \"name\" expects an existing and activated locale, \"wrong\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with wrong scope fields for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
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
                - type:        copy_value
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
                - type:        copy_value
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
    Then I should see "skipped 2"
    And I should see "actions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for copier base). Attribute \"description\" expects an existing scope, \"wrong\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |
