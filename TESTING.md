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
- frontend: Jest & Enzyme

Examples:

- backend: test the generation of the completeness from a product
- frontend: test the dropdown component

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
- frontend: Gherkin through CucumberJS & Puppeteer

Examples:

- backend/frontend: test the completeness has been calculated when the user filled in values in a product

### Integration - test the integration of a brick with the outside world

Characteristics:

- it has no mock (it tests the real classes)
- it may test several classes at the same time
- it tests only services that perform [I/O calls](#faq)

Tools:

- backend: PhpUnit
- frontend: Jest with Puppeteer

Examples:

- backend: test a Doctrine repository using MySQL
- frontend: test a fetcher performing HTTP calls

### End to end - test the whole application

Characteristics:

- it tests the application as a whole (the backend and the frontend are tested at the same time)
- it treats the application as a black box
- it has no mock (it tests the real application)
- it can require a complex setup (like a browser and Selenium for instance)
- it tests nominal use cases
- it tests critical use cases

Tools:

- Behat with Mink and Selenium

Examples:

- test that a user can fill in product values via the UI
- test that an import can be launched via the CLI
- test the response of a `/GET` product from the web API


## It's all about architecture

### The foundations: ports and adapters

The goal of the ports and adapters architecture is to segregate the inside of our application from the outside world. 
The inside is valuable and makes us earn money. The outside is only related to infrastructure concerns and should be easily interchangeable.
To communicate with the outside world, the inside relies on contracts (a _port_) that are implemented (an _adapter_) in the outside layer.

### A slight touch of Domain-Driven Design

Domain-Driven Design follows the same principles that ports and adapters regarding the layers segregation. The most important thing is that no external layer should leak into a deeper layer. The main difference is that it introduces a new layer, which means we end up with:

- _Domain_: it holds the model and all the business logic
- _Application_: it orchestrates the Domain and Infrastructure layers. It translates and validates the outside world to the Domain. It is the realm of use cases.
- _Infrastructure_: it talks with the outside world. Typically, it persists domain objects and receives user's inputs. This is where we'll find the repository implementations, the frameworks glue, everything that's related databases, HTTP and all the other ports of the system.

### The relation with the tests

It's true, that we don't use strictly ports and adapters or domain-driven design layers. So why is it useful for our test problems?
When we code something we should be able to determine which layer it would belong ideally. Is it pure domain logic? Is it related to use case? Is it an infrastructure implementation?
Once we have that in mind, we can easily determine which kind of test we should write for that particular piece of code.

Unit:

- it focuses on the _Domain_ layer

Acceptance:

- it focuses on the _Application_ layer

Integration:

- it focuses on the _Infrastructure_ layer
- it tests an Adapter

End to end:

- it crosses over all the layers, from an adapter to another by passing through the _Domain_ layer (for instance: Adapter A -> Application -> Domain -> Application -> Adapter B)


## Actual vs Expected

### Today's situation

Today, as of July the 5th 2018, we have on master:

- backend tests:
    - ~2200 end to end Behat tests
    - ~3100 integration/end to end phpUnit 
    - ~50 acceptance Behat tests
    - ~7000 phpSpec
- a very few frontend tests

Over a long period of time:

- We added end to end Behat without bothering too much. Until the situation become out of control.
- We wrote Behat tests without considering our business. We were describing a UI behavior, not a business use case.
- We confused the type of test with the tool we used. We thought for instance that using phpUnit was making integration tests.
- We accepted the builds to be longer and longer.

But nothing is lost. It's time to change!

### The ideal pyramid

To enhance the situtation, our goal should be to distribute evenly our tests as described in the following pyramid:

![Ideal tests pyramid](/tests_pyramid.png "Ideal tests pyramid")

Of course, this pyramid is not a recipe to follow blindly. The most important to understand is that, relatively to our total number of tests:

- We should have a very few end to end tests
- We should have few integration tests
- We should a lot more of acceptance and unit tests
- Frontend and backend tests should be able to live separately
- Frontend and backend tests can follow the same "testing layers"

### How to move towards the ideal pyramid?

The path towards this ideal pyramid will be long and tortuous. But still:

- We should avoid adding new end to end Behat tests in the `tests/legacy` folder. Instead, we should focus on writing acceptance tests. And yes, it will be hard, especially in the beginning.
- We should avoid adding new integration phpUnit tests that are not related to an adapter.
- We should learn how to write correct Gherkin and acceptance tests.
- We should accept that not everything needs to be tested evenly. Testing is a deliberate act and choice.
- We should try to embrace TDD: _it helps testing what we need instead of what it does_.
- We should use OOP correctly to avoid writing useless unit tests.

## FAQ

> I don't know if I should write a unit, an acceptance, an integration or an end-to-end test. What should I do?

You should refer to the [ports and adapters architecture](#i-want-to-know-more-about-that-ports-and-adapters-thing). Ask yourself, where your class is standing regarding the different layers. Then, you can refer to [this section](#the-relation-with-the-tests).

> I'm afraid to write less end to end tests than before. Are you sure it's a good idea?

For sure, end to end tests are a really safe cocoon. They strictly ensure what we have coded works as expected. But you should also remember that they become a burden when they are too numerous. As long as you have many unit and acceptance tests, as well as the necessary integration tests, you're safe. That means all your use cases are covered, and you're able to communicate with the outside world. A few system tests will only ensure that you have correctly glued all the pieces together. They do nothing more. In any case, if you have some doubt, ask the piece of advice of a teammate.

> OK, you convinced me. But now, another thing triggers my mind. When should I write an end to end test?

Try to remember the ideal tests pyramid. We should have just a few end to end to test. Only what's _critical_ should be tested. Also, only nominal use cases should be tested end to end. _Critical_ can mean different things depending on the context. It can be something simple but used everyday by a lot of people. Or it can be something used not so often but that is absolutely mandatory for the feature to work correctly: something that makes us earn money.

> What is a service that performs I/O calls?

Any service that uses an external system (relatively to your code). Can be considered as external systems: the file system, the system time, any system called via the network, a database or a search engine for instance. That means a Doctrine repository, which communicates with the database, is a service performing I/O calls.

> Testing is really cool, but our CI builds are too long. So I don't want to test anymore. What should I do?

Your frustration is completely understandable. And yes, the path towards short CI builds will be long. But we've achieved the first and the most difficult step, which was to understand how we should test correctly. Now it's just a matter of time and team motivation. A dedicated section explains how we can [improve current situation](#how-to-move-towards-the-ideal-pyramid).

## Resources

### How can I write useful and powerful Gherkin?

[Writing Good Gherkin](https://automationpanda.com/2017/01/30/bdd-101-writing-good-gherkin/) by Andrew Knight

[Telling Better Stories](http://videos.ncrafts.io/video/275529792) by David Evans

[Modelling by Example Workshop](https://fr.slideshare.net/CiaranMcNulty/modelling-by-example-workshop-phpnw-2016) by Ciaran McNulty

[Introducing Modelling by Example](http://stakeholderwhisperer.com/posts/2014/10/introducing-modelling-by-example) by Konstantin Kudryashov

### I want to know more about that ports and adapters thing!

[Hexagonal architecture](http://alistair.cockburn.us/Hexagonal%20architecture), original article by Alistair Cockburn

[Improve Your Software Architecture with Ports and Adapters](https://spin.atomicobject.com/2013/02/23/ports-adapters-software-architecture/) by Tony Baker

[Ports & Adapters Architecture](https://herbertograca.com/2017/09/14/ports-adapters-architecture/) by Herberto Graça

[Ports-And-Adapters / Hexagonal Architecture](http://www.dossier-andreas.net/software_architecture/ports_and_adapters.html)

### I want to know more about the separation layers described in Domain-Driven Design!

[Domain Driven Design Quickly](https://www.infoq.com/minibooks/domain-driven-design-quickly) by InfoQ

[Domain-Driven Design](https://herbertograca.com/2017/09/07/domain-driven-design/) by Herberto Graça
