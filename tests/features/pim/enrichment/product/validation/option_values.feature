Feature: Validate simple and multi-select attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for simple and multi select attribute

  Background:
    Given the following attributes:
      | code        | type                     |
      | sku         | pim_catalog_identifier   |
      | color       | pim_catalog_simpleselect |
      | collections | pim_catalog_multiselect  |
    And the following attribute options:
      | attribute   | code        |
      | color       | red         |
      | collections | spring_2019 |
      | collections | summer_2019 |
    And the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"

  @acceptance-back
  Scenario: Providing an existing simple select option should not raise an error
    When a product is created with values:
      | attribute | data  | scope | locale |
      | color     | red   |       |        |
    Then no error is raised

  @acceptance-back
  Scenario: Providing a non-existing simple select options should raise an error
    When a product is created with values:
      | attribute | data  | scope | locale |
      | color     | green |       |        |
    Then the error 'Property "color" expects a valid code. The option "green" does not exist' is raised

  @acceptance-back
  Scenario: Providing existing multi select options should not raise an error
    When a product is created with values:
      | attribute   | data                    | scope | locale |
      | collections | spring_2019,summer_2019 |       |        |
    Then no error is raised

  @acceptance-back
  Scenario: Providing existing options with the wrong case should not raise an error
    When a product is created with values:
      | attribute   | data                    | scope | locale |
      | color       | Red                     |       |        |
      | collections | Spring_2019,SUMMER_2019 |       |        |
    Then no error is raised

  @acceptance-back
  Scenario: Providing non-existing simple select options should raise an error
    When a product is created with values:
      | attribute   | data                                          | scope | locale |
      | collections | winter_2018,spring_2019,summer_2019,fall_2019 |       |        |
    Then the error 'Property "collections" expects valid codes. The following options do not exist: "fall_2019, winter_2018"' is raised
