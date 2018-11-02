@acceptance-back
Feature: Map some attribute options with Franklin attribute options

  Scenario: Successfully save the attribute options mapping
    Given the family "router"
    And the following attribute options for the attribute "color":
      | code   |
      | color1 |
      | color2 |
      | color3 |
    And Franklin is configured with a valid token
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             |                               | inactive |
      | color_2                      | blue                            | color2                        | active   |
      | color_3                      | yellow                          |                               | inactive |
    Then the attribute options mapping should be:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code |
      | color_1                      | red                             |                               |
      | color_2                      | blue                            | color2                        |
      | color_3                      | yellow                          |                               |
