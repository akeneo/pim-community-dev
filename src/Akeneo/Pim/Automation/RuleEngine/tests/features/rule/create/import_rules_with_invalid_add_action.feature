Feature: Validation on rules creation
  In order ease the enrichment of the catalog
  As an administrator
  I need to know when a rule is wrong

  Background:
    Given a "footwear" catalog configuration

  @integration-back
  Scenario: See validation messages when locales and scopes are not correctly set
    Given the following attribute:
      | code               | label-en_US        | type                     | scopable | localizable | group | available_locales |
      | loc_specific_color | Loc specific color | pim_catalog_simpleselect | 1        | 1           | other | fr_FR             |
    When the following yaml file is imported:
    """
    rules:
        bad_locale_and_scope:
            conditions:
                - field:    sku
                  operator: =
                  value:    test
            actions:
                - type:  add
                  field: cap_color
                  locale: unknown
                  scope: unknown
                  items: [test]
        locale_missing:
            conditions:
                - field:    sku
                  operator: =
                  value:    test
            actions:
                - type:  add
                  field: cap_color
                  scope: tablet
                  items: [test]
        scope_missing:
            conditions:
                - field:    sku
                  operator: =
                  value:    test
            actions:
                - type:  add
                  field: cap_color
                  locale: en_US
                  items: [test]
        locale_and_scope_must_not_be_set:
            conditions:
                - field:    sku
                  operator: =
                  value:    test
            actions:
                - type:  add
                  field: sku
                  locale: en_US
                  scope: tablet
                  items: [test]
        locale_does_not_bound_to_channel:
            conditions:
                - field:    sku
                  operator: =
                  value:    test
            actions:
                - type:  add
                  field: cap_color
                  locale: fr_FR
                  scope: tablet
                  items: [test]
        not_available_locale_for_locale_specific_attribute:
            conditions:
                - field:    sku
                  operator: =
                  value:    test
            actions:
                - type:  add
                  field: loc_specific_color
                  locale: en_US
                  scope: tablet
                  items: [test]
    """
    Then an exception with message "actions[0].scope: The \"unknown\" channel does not exist" has been thrown
    And an exception with message "actions[0].locale: The \"unknown\" locale does not exist or is not activated" has been thrown
    And an exception with message "actions[0].field: The \"cap_color\" attribute code is localizable and no locale is provided" has been thrown
    And an exception with message "actions[0].field: The \"cap_color\" attribute code is scopable and no channel is provided" has been thrown
    And an exception with message "actions[0].field: The \"sku\" attribute code is not localizable and a locale is provided" has been thrown
    And an exception with message "actions[0].field: The \"sku\" attribute code is not scopable and a channel is provided" has been thrown
    And an exception with message "actions[0].field: The \"fr_FR\" locale code is not bound to the \"tablet\" channel code" has been thrown
    And an exception with message "actions[0].field: The \"en_US\" locale code is not available for the \"loc_specific_color\" locale specific attribute code" has been thrown
    And the rule list does not contain the "bad_locale_and_scope" rule
    And the rule list does not contain the "locale_missing" rule
    And the rule list does not contain the "scope_missing" rule
    And the rule list does not contain the "locale_and_scope_must_not_be_set" rule
    And the rule list does not contain the "locale_does_not_bound_to_channel" rule
    And the rule list does not contain the "not_available_locale_for_locale_specific_attribute" rule
