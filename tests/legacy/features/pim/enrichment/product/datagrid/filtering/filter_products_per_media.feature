@javascript
Feature: Filter products per media
  In order to easily find products with specific media
  As a regular user
  I need to be able to filter products per media

  Scenario: Successfully filter products by image and file attributes
    Given the "default" catalog configuration
    And the following attributes:
      | code       | label-en_US | type              | useable_as_grid_filter | allowed_extensions | group |
      | image      | Image       | pim_catalog_image | 1                      | gif,png,jpeg,jpg   | other |
      | attachment | Attachment  | pim_catalog_file  | 1                      | txt                | other |
    And the following family:
      | code    | attributes           |
      | tshirts | sku,image,attachment |
    And the following products:
      | sku         | family  |
      | shirt-one   | tshirts |
      | shirt-two   | tshirts |
      | shirt-three | tshirts |
      | shirt-four  | tshirts |
    And the following product values:
      | product     | attribute  | value                              |
      | shirt-one   | image      | %fixtures%/akeneo.jpg              |
      | shirt-two   | image      | %fixtures%/bic-core-148.gif        |
      | shirt-three | image      | %fixtures%/fanatic-freewave-76.gif |
      | shirt-one   | attachment | %fixtures%/akeneo.txt              |
      | shirt-two   | attachment | %fixtures%/fanatic-freewave-76.txt |
    And I am logged in as "Mary"
    When I am on the products grid
    Then the grid should contain 4 elements
    And I should see products shirt-one, shirt-two, shirt-three and shirt-four
    And I should be able to use the following filters:
      | filter     | operator         | value      | result                    |
      | image      | starts with      | a          | shirt-one                 |
      | image      | contains         | ic         | shirt-two and shirt-three |
      | attachment | does not contain | neo        | shirt-two                 |
      | image      | is equal to      | akeneo.jpg | shirt-one                 |
      | attachment | is empty         |            | shirt-four and three      |
      | attachment | is not empty     |            | shirt-one and shirt-two   |
