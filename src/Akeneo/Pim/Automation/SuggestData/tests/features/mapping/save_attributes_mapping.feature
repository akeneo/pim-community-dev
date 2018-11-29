@acceptance-back
Feature: Map the PIM attributes with Franklin attributes
  In order to automatically enrich my products
  As a system administrator
  I want to map Franklin attributes to Akeneo PIM attributes

#  @critical
#  Scenario: Successfully save the attributes mapping
#    Given the family "router"
#    And the following attribute:
#      | code          | type             |
#      | product_color | pim_catalog_text |
#    And Franklin is configured with a valid token
#    When the attributes are mapped for the family "router" as follows:
#      | target_attribute_code | pim_attribute_code | status  |
#      | product_weight        |                    | pending |
#      | color                 | product_color      | active  |
#    Then the retrieved attributes mapping for the family "router" should be:
#      | target_attribute_code | target_attribute_label | pim_attribute_code | status  |
#      | product_weight        | Product Weight         |                    | pending |
#      | color                 | Color                  | product color      | active  |

    #Then Franklin attribute "product_weight" should not be mapped (inactive)
    #And Franklin attribute "color" should be mapped to PIM "product_color" (activated)
