@acceptance-back
Feature: Map the PIM attributes with PIM.ai attributes
  In order to automatically enrich my products
  As a system administrator
  I want to map PIM.ai attributes to Akeneo PIM attributes

  Scenario: Successfully save the attributes mapping
    Given the family "router"
    And the following attribute:
      | code  | type             |
      | product_color | pim_catalog_text |
    And PIM.ai is configured with a valid token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        |                    | pending |
      | color                 | product_color      | active  |
    Then the retrieved attributes mapping for the family "router" should be:
      | target_attribute_code | target_attribute_label | pim_attribute_code | status  |
      | product_weight        | Product Weight         |                    | pending |
      | color                 | Color                  | product color      | active  |
