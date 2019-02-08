@acceptance-back
Feature: Map some attribute options with Franklin attribute options

  @critical @end-to-end @javascript
  Scenario: Successfully map an attribute option for the first time
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    And the predefined options "color1, color2 and color3" for the attribute "color"
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | red                          | red                             |                               | inactive |
      | blue                         | blue                            | color1                        | active   |
      | black                        | black                           |                               | inactive |
    Then Franklin option red should not be mapped
    And Franklin option blue should be mapped to color1
    And Franklin option black should not be mapped

  Scenario: Successfully update an existing attribute options mapping
    Given the family "router"
    And Franklin is configured with a valid token
    And the predefined options "color1, color2 and color3" for the attribute "color"
    And a predefined options mapping between Franklin attribute "product color" and PIM attribute "color" for family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    When the Franklin "product color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
      | color_2                      | blue                            | color2                        | active   |
      | color_3                      | yellow                          |                               | inactive |
    Then Franklin option color_1 should be mapped to color1
    And Franklin option color_2 should be mapped to color2
    And Franklin option color_3 should not be mapped

  Scenario: Successfully map an attribute option on nothing
    Given the family "router"
    And Franklin is configured with a valid token
    And the predefined options color2 for the attribute "color"
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             |                               | inactive |
      | color_2                      | blue                            | color2                        | active   |
    Then Franklin option color_1 should not be mapped
    And Franklin option color_2 should be mapped to color2

  Scenario: Fail to map all attribute options to nothing
    Given the family "router"
    And Franklin is configured with a valid token
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             |                               | inactive |
      | color_2                      | blue                            |                               | inactive |
    Then an empty attribute options mapping message should be sent
    And the attribute options mapping should not be saved

  Scenario: Fail to map attribute options with an expired token
    Given the family "router"
    And the predefined options color1 for the attribute "color"
    And Franklin is configured with an expired token
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved
    And an authentication error message should be sent

  Scenario: Fail to map an attribute options when Franklin server is down
    Given the family "router"
    And the predefined options color1 for the attribute "color"
    And Franklin is configured with a valid token
    And Franklin server is down
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved
    And a data provider error message should be sent

  Scenario: Fail to map an attribute options if the family does not exist
    Given the family "router"
    And the predefined options color1 for the attribute "color"
    And Franklin is configured with a valid token
    When the Franklin "color" options are mapped to the PIM "color" options for the family "unknown" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved
    And an unknown family message should be sent

  Scenario: Fail to map an attribute options with an unknown attribute
    Given the family "router"
    And the predefined options color1 for the attribute "color"
    And Franklin is configured with a valid token
    And Franklin server is down
    When the Franklin "color" options are mapped to the PIM "unknown" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved
    And an unknown attribute message should be sent

  Scenario: Fail to map an attribute options that does not belong to the attribute
    Given the family "router"
    And the following attribute:
      | code            | type                    |
      | wrong_attribute | pim_catalog_multiselect |
    And the predefined options color1 for the attribute "wrong_attribute"
    And Franklin is configured with a valid token
    And Franklin server is down
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved
    And a wrong option attribute message should be sent

  Scenario: Fail to save an empty attribute options mapping
    Given the family "router"
    And Franklin is configured with a valid token
    When the Franklin "color" options are mapped to the PIM "router" "color" options with an empty mapping
    Then the attribute options mapping should not be saved
    And an empty attribute options mapping message should be sent

