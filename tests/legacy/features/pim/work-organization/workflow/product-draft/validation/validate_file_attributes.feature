@javascript @info More user-friendly validation to be done in the scope of @jira https://akeneo.atlassian.net/browse/PIM-2029
Feature: Validate file attributes of a draft
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for file attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code       | label-en_US | type             | scopable | max_file_size | allowed_extensions | group |
      | brief      | Brief       | pim_catalog_file | 0        | 0.01          | txt                | info  |
      | attachment | Attachment  | pim_catalog_file | 1        | 0.01          | txt                | info  |
    And the following family:
      | code | label-en_US | attributes           |
      | baz  | Baz         | sku,brief,attachment |
    And the following product:
      | sku | family | categories        |
      | foo | baz    | summer_collection |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the max file size constraint of file attribute
    Given I attach file "huge.txt" to "Brief"
    And I save the product
    Then I should see the text "The file huge.txt is too large (11.03 kB). The brief attribute can not exceed 10 kB."

  Scenario: Validate the max file size constraint of scopable file attribute
    Given I attach file "huge.txt" to "Attachment"
    And I save the product
    Then I should see the text "The file huge.txt is too large (11.03 kB). The attachment attribute can not exceed 10 kB."

  Scenario: Validate the allowed extensions constraint of file attribute
    Given I attach file "fanatic-freewave-76.gif" to "Brief"
    And I save the product
    Then I should see the text "The gif file extension is not allowed for the brief attribute. Allowed extensions are txt."

  Scenario: Validate the allowed extensions constraint of scopable file attribute
    Given I attach file "fanatic-freewave-76.gif" to "Attachment"
    And I save the product
    Then I should see the text "The gif file extension is not allowed for the attachment attribute. Allowed extensions are txt."
