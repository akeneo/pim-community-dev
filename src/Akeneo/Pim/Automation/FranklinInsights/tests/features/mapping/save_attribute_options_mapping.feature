@acceptance-back
Feature: Map some attribute options with Franklin attribute options

  Scenario: Successfully map an attribute option for the first time
    Given the family "router"
    And Franklin is configured with a valid token
    And the following attribute options for the attribute "color":
      | code   |
      | color1 |
      | color2 |
      | color3 |
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

  Scenario: Successfully update an existing attribute options mapping
    Given the family "router"
    And Franklin is configured with a valid token
    And the following attribute options for the attribute "color":
      | code   |
      | color1 |
      | color2 |
      | color3 |
    And a predefined options mapping between Franklin attribute "product color" and PIM attribute "color" for family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    When the Franklin "product color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
      | color_2                      | blue                            | color2                        | active   |
      | color_3                      | yellow                          |                               | inactive |
    Then the attribute options mapping should be:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code |
      | color_1                      | red                             | color1                        |
      | color_2                      | blue                            | color2                        |
      | color_3                      | yellow                          |                               |

  Scenario: Successfully map an attribute option on null
    Given the family "router"
    And Franklin is configured with a valid token
    And the following attribute options for the attribute "color":
      | code   |
      | color2 |
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             |                               | inactive |
      | color_2                      | blue                            | color2                        | active   |
    Then the attribute options mapping should be:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code |
      | color_1                      | red                             |                               |
      | color_2                      | blue                            | color2                        |

  Scenario: Successfully map all attribute options to null
    Given the family "router"
    And Franklin is configured with a valid token
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             |                               | inactive |
      | color_2                      | blue                            |                               | inactive |
    Then the attribute options mapping should be:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code |
      | color_1                      | red                             |                               |
      | color_2                      | blue                            |                               |

  Scenario: Fail to map attribute options with an expired token
    Given the family "router"
    And the following attribute options for the attribute "color":
      | code   |
      | color1 |
    And Franklin is configured with an expired token
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved

  Scenario: Fail to map an attribute options when Franklin server is down
    Given the family "router"
    And the following attribute options for the attribute "color":
      | code   |
      | color1 |
    And Franklin is configured with a valid token
    And Franklin server is down
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved

  Scenario: Fail to map an attribute options if the family does not exist
    Given the family "router"
    And the following attribute options for the attribute "color":
      | code   |
      | color1 |
    And Franklin is configured with a valid token
    When the Franklin "color" options are mapped to the PIM "color" options for the family "unknown" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved

  Scenario: Fail to map an attribute options with an unknown attribute
    Given the family "router"
    And the following attribute options for the attribute "color":
      | code   |
      | color1 |
    And Franklin is configured with a valid token
    And Franklin server is down
    When the Franklin "color" options are mapped to the PIM "unknown" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved

  Scenario: Fail to map an attribute options that does not belong to the attribute
    Given the family "router"
    And the following attribute:
      | code            | type                    |
      | wrong_attribute | pim_catalog_multiselect |
    And the following attribute options for the attribute "wrong_attribute":
      | code   |
      | color1 |
    And Franklin is configured with a valid token
    And Franklin server is down
    When the Franklin "color" options are mapped to the PIM "color" options for the family "router" as follows:
      | franklin_attribute_option_id | franklin_attribute_option_label | catalog_attribute_option_code | status   |
      | color_1                      | red                             | color1                        | active   |
    Then the attribute options mapping should not be saved

  Scenario: Fail to save an empty attribute options mapping
    Given the family "router"
    And Franklin is configured with a valid token
    When the Franklin "color" options are mapped to the PIM "router" "color" options with an empty mapping
    Then the attribute options mapping should not be saved

