Feature: List products linked to a record
  In order to know which products are linked to a record
  As a user
  I want see the first selection of products for a record

  @acceptance-front
  Scenario: Listing linked products
    Given a valid record
    And the user has the following rights:
      | akeneo_referenceentity_record_list_product | true |
    When the user asks for the list of linked product
    Then the user should see the list of products linked to the record

  @acceptance-front
  Scenario: Listing linked products without any reference entity attribute
    Given a valid record
    And the user has the following rights:
      | akeneo_referenceentity_record_list_product | true |
    When the user asks for the list of linked product without any reference entity attribute
    Then the user should not see any linked product attribute

  @acceptance-front
  Scenario: Listing linked products without any linked products
    Given a valid record
    And the user has the following rights:
      | akeneo_referenceentity_record_list_product | true |
    When the user asks for the list of linked product without any linked product
    Then the user should not see any linked product
