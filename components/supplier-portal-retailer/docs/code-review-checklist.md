# Code review checklist

**Here is a list of questions to ask yourself during code reviews:**

## Functionality

- Do I understand the logic (domain)?

## Software Design

- Is the overall code well thought out?
- Are the namespaces of the new classes correct? Is the hexagonal architecture respected?
- Are the names of the new classes explicit?
- Is the service declaration made from an interface in the `domain`?
- Are there any `infra` dependencies in `application` and `domain`?
- Is there any business code in the `infra`?
- Is there a `Query` in the PR? If so, does it implement an interface? Is it tested in integration?
- Are the design patterns used relevant/correctly used?

## Complexity

- Isn't the code more complicated than necessary?
- Are there no premature optimizations for a potential future need?

## Tests

More information on [our test strategy documentation](./tests/introduction.md).

## Scaling

- Does the code scale? You can check volumetry on [Metabase](https://akeneo.metabaseapp.com/) + ensure that limits are set.

## Migration

- Is there a migration? If yes, are they well named? Are they idempotent? Are they tested in integration as expected following the PIM rule?

## Documentation

- Does the technical documentation need to be updated?
- Are comments clear and useful? Concretely, do they explain the why and not the what?

## Good Things

- Don't hesitate to add a comment if you find a well done implem', a very clean code... a code review does not only focus on the problems but also share the good practices 🙂
