- See if it's possible to have the routing declared only in our bundle (we will see later with black hawks how to do it)
- Application/EnrichedEntity/Show/ShowEnrichedEntityHandler should handle only one command it should maybe return only a ReadModel
- Discuss about Domain/Model/EnrichedEntity/EnrichedEntity getters: they break the tell don't ask principle.
- rename Domain/Model/EnrichedEntity/EnrichedEntityIdentifier:fromString to create: we cannot create them another way
- create a read model for the read cases
- Move the InMemory repository in the business code
- Create builders for entities in the backend
- Create LabelCollection from outside the domain object EnrichedEntity
- What to do in a show command handler if the entity is not found? Throw an exception? null?
- Should we use "list" or "index"? Should we use "get" or "show"? In a lot of places we use one or the other. Could be nice to choose before it's getting too messy
- add normalizer for enriched entity
- add session storage for tab situation
- rework acceptance tests when the locale switcher will work
- fix the form to take into account what the backend answered
- test integration controller edit
- add pqb filter, grid filter and peb filter for the enriched entity value
- Add remaining ACLs
- add keyboard shortcuts
- rework the record identifier to have one identifier
- locale switcher to be able to edit all locales
- enforce html event type
- Change read models to enforce properties are coherent
- Extract the buttons of the edit enriched entity view so it comes from each of the tabs
- Rename "AttributeRequired" by "AttributeIsRequired"

Specific back-end:
- for imports of attributes (case never happens with UI): Add validation of editCommands depending on the property updated, check the type (text/image) if it's supported (today an error is thrown saying it didn't find an updater by the registry)
  (See validation of "Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommand" and the primary constraint)

DONE:

- rename "I get an enriched entity" acceptance step [DONE]
- manage breadcrumb [DONE]
- add loading placeholder [DONE]
- rename hidrate -> hydrate [DONE]
- rework form.tsx to not remove labels [DONE]
- remove `back` from the php namespace [DONE]
- use decorator for acceptance tests front [DONE]
- clean classes on form [DONE]
- fix validation error display [DONE]
- enforce getState type [DONE]
