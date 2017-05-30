@javascript @info More user-friendly validation to be done in the scope of @jira https://akeneo.atlassian.net/browse/PIM-2029
Feature: Validate image attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for image attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label-en_US | type              | scopable | max_file_size | allowed_extensions | group |
      | image     | Image       | pim_catalog_image | 0        | 0.01          | jpg                | other |
      | thumbnail | Thumbnail   | pim_catalog_image | 1        | 0.01          | jpg                | other |
    And the following family:
      | code | label-en_US | attributes          |
      | baz  | Baz         | sku,image,thumbnail |
    And the following product:
      | sku | family |
      | foo | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the max file size constraint of image attribute
    Given I attach file "akeneo.jpg" to "Image"
    And I save the product
    Then I should see the text "The file is too large (10.58 kB). Allowed maximum size is 10 kB."

  Scenario: Validate the max file size constraint of scopable image attribute
    Given I switch the scope to "ecommerce"
    And I attach file "akeneo.jpg" to "Thumbnail"
    And I save the product
    Then I should see the text "The file is too large (10.58 kB). Allowed maximum size is 10 kB."

  Scenario: Validate the allowed extensions constraint of image attribute
    Given I attach file "fanatic-freewave-76.gif" to "Image"
    And I save the product
    Then I should see the text "The file extension is not allowed (allowed extensions: jpg)."

  Scenario: Validate the allowed extensions constraint of scopable image attribute
    Given I switch the scope to "ecommerce"
    And I attach file "fanatic-freewave-76.gif" to "Thumbnail"
    And I save the product
    Then I should see the text "The file extension is not allowed (allowed extensions: jpg)."
