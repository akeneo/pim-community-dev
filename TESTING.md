# Testing in the PIM

## About

This document aims to help the writing of tests in the PIM. It's the result of training sessions and workshops we had with [Matthias Noback](https://matthiasnoback.nl/) around this topic. This guide applies for both backend and frontend applications.


## Types of tests

Let's remind here the characteristics of each type of tests, the tools we must use from now on and give some concrete examples to help the reader.

### Unit - test a unit of code

Most of the time, a unit of code is a class.

Characteristics:

- it tests only one class at a time
- it performs no [I/O call](#faq)
- it doesn't require any setup (no fixtures for instance)
- it mocks only what we own
- it "lives" in memory only

Tools:

- backend: PhpSpec
- frontend: TODO

Examples:

- backend: TODO
- frontend: TODO

### Acceptance - test a business use case or ensure a business rule

Characteristics:

- it tests several classes at the same time
- it uses the business language, which means _Gherkin_ must be used
- it describes a business use case or it ensures a business rule (it's not about UI, CLI or UX, neither about a text we should see)
- it mocks only what we own
- it mocks services performing [I/O calls](#faq)
- it "lives" in memory only

Tools:

- backend: Gherkin through Behat (no Mink, no Selenium)
- frontend: Gherkin through TODO

Examples:

- backend: TODO
- frontend: TODO

### Integration - test the integration of a brick with the outside world

Characteristics:

- it has no mock (it tests the real classes)
- it may test several classes at the same time
- it tests only services that perform [I/O calls](#faq)

Tools:

- backend: PhpUnit
- frontend: TODO

Examples:

- backend: test a Doctrine repository using MySQL
- frontend: test a fetcher performing HTTP calls

### End to end - test the whole application

Characteristics:

- it tests the application as a whole (the backend and the frontend are tested at the same time)
- it has no mock (it tests the real application)
- it can require a complex setup (like a browser and Selenium for instance)
- it tests nominal use cases

Tools:

- Behat with Mink and Selenium

Examples:

- test that a user can fill in product values via the UI
- test that an import can be launched via the CLI


## It's all about architecture

### The foundations: ports and adapters



### The relation with the tests

Unit:

- it focuses on the Domain layer

Acceptance:

- it focuses on the Application layer

Integration:

- it focuses on the Infrastructure layer
- it tests an Adapter

End to end:

- it crosses over all the layers, from an adapter to another by passing through the Domain layer (for instance: Adapter A -> Application -> Domain -> Application -> Adapter B)


## Actual vs Expected

### Today's situation

### The ideal pyramid

![Ideal tests pyramid](/tests_pyramid.png "Ideal tests pyramid")


## FAQ

> I don't know if I should write a unit, an acceptance, an integration or an end-to-end test. What should I do?

You should refer to the [ports and adapters architecture](#ports-and-adapters-architecture). Ask yourself, where your class is standing regarding the different layers. Then, you can refer to [this section](#the-relation-with-the-tests).

> I'm afraid to write less end to end tests that before. Are you sure it's a good idea?

For sure, end to end tests are a really safe cocoon. They strictly ensure what we have coded works as expected. But you should also remember that they become a burden when they are too numerous. As long as you have many unit and acceptance tests, as well as the necessary integration tests, you're safe. That means all your use cases are covered, and you're able to communicate with the outside world. A few system tests will only ensure that you have correctly glued all the pieces together. They do nothing more. In any case, if you have some doubt, ask the piece of advice of a teammate.

> What is a service that performs I/O calls?

Any service that uses an external system (relatively to your code). Can be considered as external systems: the file system, the system time, any system called via the network, a database or a search engine for instance. That means a Doctrine repository, which communicates with the database, is a service performing I/O calls.

## Resources

> How can I write useful and powerful Gherkin?

[Modelling by Example Workshop](https://fr.slideshare.net/CiaranMcNulty/modelling-by-example-workshop-phpnw-2016) by Ciaran McNulty

> I want to know more about that ports and adapters thing!

[Hexagonal architecture](http://alistair.cockburn.us/Hexagonal%20architecture), original article by Alistair Cockburn

[Improve Your Software Architecture with Ports and Adapters](https://spin.atomicobject.com/2013/02/23/ports-adapters-software-architecture/) by Tony Baker

[Ports & Adapters Architecture](https://herbertograca.com/2017/09/14/ports-adapters-architecture/) by Herberto Graça

[Ports-And-Adapters / Hexagonal Architecture](http://www.dossier-andreas.net/software_architecture/ports_and_adapters.html)
