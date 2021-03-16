# Project overview

- [Frontend architecture history](#frontend-architecture-history)
- [Legacy stack](#legacy-stack)
  - [Backbone](#backbone)
  - [BEM](#bem-deprecated)
- [React stack](#react-stack)
  - [External packages](#external-packages-📦)
  - [Internal packages](#internal-packages)
  - [React tests](#react-tests)
- [Akeneo Design System](#akeneo-design-system)

## Frontend architecture history

1. Akeneo PIM was created with the framework [Symfony](https://symfony.com/) (2.1) and OroPlatform, using twig templates and RequireJS to load javascript modules.

   Those javascript modules used Backbone to render views and "form extensions" to compose and extend the UI.

1. The Block Element Modifier (BEM) methodology was introduced, to replace and organize properly all the CSS stylesheets.

1. Typescript was introduced for new development to benefit from static typing and avoid a whole class of bugs, as a results javascript is rarely used directly anymore.

   The team started to convert javascript modules to typescript when necessary.

1. The team introduced React to develop new features or easily embed components inside existing backbone pages.

   The goal with React was to get closer to the mainstream stacks and improve the developer experience as well as the onboarding of new teammates. Also, with a SaaS first approach, the ability to extends directly the PIM UI is gone.

1. The new [Design System](https://en.wikipedia.org/wiki/Design_system) created by our UX team started to be implemented in React and allow to re-use standard components everywhere.

   The BEM was deprecated as the new Design System become the source of truth for the UI style guide.

## Legacy stack

### Backbone

Take a look at the [Akeneo PIM Frontend Guide (medium.com/akeneo-labs)](https://medium.com/akeneo-labs/akeneo-pim-frontend-guide-part-1-bd398b6483a2) for a detailed explanation on the architecture and utilization of Backbone inside the PIM.

The documentation (< 4.0) [Design the user interfaces (docs.akeneo.com)](https://docs.akeneo.com/4.0/design_pim/index.html) is also available for the oldest part of the PIM.

### BEM

The "BEM" refers to the UI styles reimplemented in [less](http://lesscss.org/) and following the [Block Element Modifier methodology](http://getbem.com/).

The documentation is available on [docs.akeneo.com](https://docs.akeneo.com/4.0/design_pim/styleguide/index.html#Overview) (outdated).

As of now, the BEM is deprecated and **not** the source of truth for the UI styles, the DSM should be used instead whenever possible.

It is still allowed to use the BEM in Backbone views, and update it if necessary.

## React stack

### External packages 📦

This is a list of some of the external libraries that exist inside the PIM.

- `react-hook-form` ([react-hook-form.com](https://react-hook-form.com/))

  To create and manage forms more easily.

  Do not use `formik` (deprecated).

- `styled-component` ([styled-components.com](https://styled-components.com/))

  To style everything that is not available as a component from the Design System.

- `redux` ([redux.js.org](https://redux.js.org/) & `redux-thunk` (EE)

  To manage complex states. Must be used with care as most of the time simple states & hooks are enough.

- `react-router` ([reactrouter.com](https://reactrouter.com/))

  To define frontend routing at the app level, without relying on the Symfony route definition.

- `victory` ([formidable.com](https://formidable.com/open-source/victory/))

  Victory to make charts.


- `jquery`

  Try to avoid jQuery. As of today, most of what jQuery offer can be done easily with the standard library.

- Use es6 `fetch` to make HTTP calls

  Documentation of the [Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)

These libraries are not mandatory to use (not every form requires `react-hook-form` nor `redux`).

### Internal packages

- `@akeneo-pim-community/legacy-bridge` ([sources](https://github.com/akeneo/pim-community-dev/tree/master/src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge))

  Define a Backbone controller & view to bridge easily with React, as well as all the services coming from the Backbone stack (router, notify, security, translate, ...) that can be injected inside a React app.

- `@akeneo-pim-community/shared` ([sources](https://github.com/akeneo/pim-community-dev/tree/master/src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared))

  Give access to some utility hooks, components, ...

### React Tests



## Akeneo Design System

Check out the [Akeneo Design System Repository](https://github.com/akeneo/pim-community-dev/tree/master/akeneo-design-system#akeneo-design-system-repository) documentation.

The reference environment is available at https://akeneo.github.io/akeneo-design-system/

Ask your engineering manager if you are interested in training on the Akeneo Design System.
