@javascript
Feature: Filter products per media
  In order to easily find products with specific media
  As a regular user
  I need to be able to filter products per media

  Scenario: Successfully filter products by image and file attributes
    Given the "default" catalog configuration
    And the following attributes:
      | code       | label-en_US | type  | useable_as_grid_filter | allowed_extensions |
      | image      | Image       | image | yes                    | gif,png,jpeg,jpg  |
      | attachment | Attachment  | file  | yes                    | txt               |
    And the following family:
      | code    | attributes             |
      | tshirts | sku, image, attachment |
    And the following products:
      | sku         | family  |
      | shirt-one   | tshirts |
      | shirt-two   | tshirts |
      | shirt-three | tshirts |
    And the following product values:
      | product     | attribute  | value                              |
      | shirt-one   | image      | %fixtures%/akeneo.jpg              |
      | shirt-two   | image      | %fixtures%/bic-core-148.gif        |
      | shirt-three | image      | %fixtures%/fanatic-freewave-76.gif |
      | shirt-one   | attachment | %fixtures%/akeneo.txt              |
      | shirt-two   | attachment | %fixtures%/fanatic-freewave-76.txt |
    And I am logged in as "Mary"
    When I am on the products page
    Then the grid should contain 3 elements
    And I should see products shirt-one, shirt-two and shirt-three
    And I should be able to use the following filters:
      | filter     | value                | result                    |
      | Image      | starts with a        | shirt-one                 |
      | Attachment | ends with txt        | shirt-one and shirt-two   |
      | Image      | contains ic          | shirt-two and shirt-three |
      | Attachment | does not contain neo | shirt-two                 |
      # todo: uncomment the following line when https://akeneo.atlassian.net/browse/PIM-3407 is fixed
      # | Image      | is equal to akeneo.jpg | shirt-one                 |
      | Attachment | empty | shirt-three |
