# Testing in the PIM


## Types of tests

### Unit - test a unit of code (usually, it's a class)

Characteristics:

- one class is tested at a time
- no I/O call
- no setup required (ie: no fixtures for instance)
- mock only what you own
- "lives" in memory only

Examples:


### Acceptance: test a business use case

Characteristics:

- several classes are tested at the same time
- business language is used (ie: we use Gherkin)
- describes a business problem we want to solve (ie: it's not about UI, CLI or UX)
- mock only what you own
- services which perform I/O calls are mocked (like Doctrine repositories for instance)
- "lives" in memory only

Examples:


### Integration: test

Characteristics:

- one class is tested at a time
- no mock
- tests only services that perform I/O calls (like Doctrine repositories for instance)

Examples:


### End to end: test the whole application

Characteristics:

- boots the whole application

Examples:

- from UI to database
- from CLI to database
- etc..


## Ports and adapters architecture

### What's that?

### The relation with the tests

Unit:

- focuses on the Domain layer

Acceptance:

- focuses on the Acceptance layer

Integration:

- focuses on the Infrastructure layer

End to end:

- crosses over all the layers, from an adapter to another by passing through the Domain layer (for instance: Adapter A -> Application -> Domain -> Application -> Adapter B)


## Actual VS Expected

### The ideal pyramid



## FAQ

> I don't know if I should write a unit, an acceptance, an integration or an end-to-end test. What should I do?

You should refer to the [ports and adapters architecture](#ports-and-adapters-architecture). Ask yourself, where your class is standing regarding the different layers. Then, you can refer to [this section](#what-is-my-test-about).

> I'm afraid to write less end to end tests that before. Are you sure it's a good idea?

For sure, end to end are a really safe cocoon. They strictly ensure what we have coded works as expected. But you should also remember that it becomes a burden when they are too numerous. As long as you 

> What is service that performs I/O calls?

Any service that uses an external system (relatively to your code). Can be considered as external systems: the file system, the system time, any system called via the network, a database or a search engine for instance. That means a Doctrine repository, which communicate with the database, is a service performing I/O calls.
