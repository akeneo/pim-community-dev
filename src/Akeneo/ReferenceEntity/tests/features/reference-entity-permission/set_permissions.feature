Feature: Set permissions for a reference entity
  In order to manage which user group is able to edit a reference entity
  As a catalog manager
  I want to set the permissions of a reference entity

  @acceptance-back @acceptance-front @nominal
  Scenario: Set permissions for a reference entity and multiple user groups
    Given a reference entity
    And the user has the following rights:
      | akeneo_referenceentity_reference_entity_manage_permission | true |
    When the user sets the following permissions for the reference entity:
      | user_group_identifier | right_level |
      | IT support            | view        |
      | Catalog Manager       | edit        |
    Then there should be a 'view' permission right for the user group 'IT support' on the reference entity
    And there should be a 'edit' permission right for the user group 'Catalog Manager' on the reference entity
