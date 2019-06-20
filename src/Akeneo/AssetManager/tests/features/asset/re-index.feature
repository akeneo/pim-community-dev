Feature: Reindex the records
  In order to search through records
  As a system administrator
  I want to re-index the records of the Pim

  @acceptance-back @success
  Scenario: Re-index all the records of a specific reference entity
    Given the reference entity "designers"
    And none of the records of "designers" are indexed
    When the system administrator reindexes all the records of "designers"
    Then the records of the reference entity "designers" have been indexed

  @acceptance-back @error
  Scenario: Cannot re-index records with wrong reference entity identifier
    When the system administrator reindexes the records of a reference entity that does not exist
    Then there should be a validation error with message 'The reference entity "unknown_reference_entity" was not found.'
