- remove `back` from the php namespace
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
- use decorator for acceptance tests front
- add session storage for tab situation
- rework acceptance tests when the locale switcher will work
- fix the form to take into account what the backend answered

DONE:

- rename "I get an enriched entity" acceptance step [DONE]
- manage breadcrumb [DONE]
- add loading placeholder [DONE]
- rename hidrate -> hydrate [DONE]
- rework form.tsx to not remove labels [DONE]
