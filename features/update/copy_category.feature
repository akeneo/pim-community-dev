Feature: Update category fields
  In order to update products
  As an internal process or any user
  I need to be able to copy a category field of a product

  Scenario: Successfully update a text field
    Given a "apparel" catalog configuration
    And the following products:
      | sku                 | categories                                 |
      | AKN_FROM            | men_2015_spring, men_2015_summer, men_2014 |
      | AKN_NOT_CATEGORIZED |                                            |
      | AKN_CATEGORIZED     | men_2013                                   |
    Then I should get the following products after apply the following updater to it:
      | product             | actions                                                                                                                                            | result                                                             |
      | AKN_FROM            | [{"type": "copy_category", "from_product": "AKN_FROM", "to_product": "AKN_NOT_CATEGORIZED", "from_field": "categories", "to_field": "categories"}] | {"categories": ["men_2015_spring", "men_2015_summer", "men_2014"]} |
      | AKN_FROM            | [{"type": "copy_category", "from_product": "AKN_FROM", "to_product": "AKN_CATEGORIZED", "from_field": "categories", "to_field": "categories"}]     | {"categories": ["men_2015_spring", "men_2015_summer", "men_2014"]} |
