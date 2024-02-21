@javascript
Feature: Mass Edit Families
  In order to define common data between families
  As an administrator
  I need to be able to mass edit attributes and requirements of families

  Scenario: Successfully add many attributes with their requirements to many families
    Given the "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the families grid
    # These families don't have attribute Length
    When I select rows Boots, Sneakers and Sandals
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I add available attributes Length
    And I switch the attribute "length" requirement in channel "mobile"
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    Then attribute "Length" should be required in family "boots" for channel "Mobile"
    And attribute "Length" should be required in family "sneakers" for channel "Mobile"
    And attribute "Length" should be required in family "sandals" for channel "Mobile"
    But attribute "Length" should be optional in family "boots" for channel "Tablet"
    And attribute "Length" should be optional in family "sneakers" for channel "Tablet"
    And attribute "Length" should be optional in family "sandals" for channel "Tablet"

  Scenario: Successfully mass edit attribute requirements by attribute group
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families grid
    When I select rows Boots, Sneakers and Sandals
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I add attributes by group "Marketing"
    And I should see attributes "Price, Rate of sale and Rating" in group "Marketing"
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    Then attribute "price" should be optional in family "boots" for channel "Tablet"
    Then attributes "price, rate_sale and rating" should be optional in family "boots" for channel "Tablet"
    And attributes "price, rate_sale and rating" should be optional in family "sneakers" for channel "Tablet"
    And attributes "price, rate_sale and rating" should be optional in family "sandals" for channel "Tablet"
    And attributes "price, rate_sale and rating" should be optional in family "boots" for channel "Mobile"
    And attributes "price, rate_sale and rating" should be optional in family "sneakers" for channel "Mobile"
    And attributes "price, rate_sale and rating" should be optional in family "sandals" for channel "Mobile"

# @jira https://akeneo.atlassian.net/browse/PIM-10853
  Scenario: Successfully mass edit unique value attributes to many families
    Given the "footwear" catalog configuration
    And the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1 |
      | family_variant | boots  | color          | description          |
    And the following attributes:
      | code                  | type             | group | unique |
      | my_unique_attribute   | pim_catalog_text | other | 1      |
    And I am logged in as "Julia"
    And I am on the families grid
    When I select rows Boots
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I add available attributes my_unique_attribute
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    Then there should be the following family variants:
      | code                | family   | variant-axes_1| variant-attributes_1                      |
      | family_variant      | boots    | color         | color,description,sku,my_unique_attribute |
