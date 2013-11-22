@javascript
Feature: Filter import profiles
  In order to filter import profiles in the catalog
  As a user
  I need to be able to filter import profiles in the catalog

  Scenario: Successfully filter import profiles
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the imports page
    Then the grid should contain 6 elements
    And I should see export profiles footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import
    And I should be able to use the following filters:
      | filter    | value                | result                                                                                                                                                      |
      | Code      | at                   | footwear_association_import, footwear_attribute_import and footwear_category_import                                                                         |
      | Label     | Product              | footwear_product_import                                                                                                                                     |
      | Job       | group_import         | footwear_group_import                                                                                                                                       |
      | Connector | Akeneo CSV Connector | footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import |
      | Status    | Ready                | footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import |
