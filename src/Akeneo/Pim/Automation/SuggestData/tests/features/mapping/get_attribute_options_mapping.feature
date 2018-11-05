@acceptance-back
Feature: Map some attribute options with Franklin attribute options

  Scenario: Successfully retrieve the attribute options mapping
    Given the family "router"
    And Franklin is configured with a valid token
    When I retrieve the attribute options mapping for the family "router" and the attribute "color"
    Then the retrieved attribute options mapping should be:
      | franklin_attribute_id | catalog_attribute_code | status   |
      | color_1               |                        | pending  |
      | color_2               | color2                 | active   |
      | color_3               |                        | inactive |
