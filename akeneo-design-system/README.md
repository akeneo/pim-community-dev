# Akeneo Design System Repository

## What is a design system?

The aim of Akeneo Design System is to help Akeneo designers and developers deliver work faster and better.
It provides teams with a common language and encourages adherence to design guidelines with compliant components while offering consistent experience to users.

Designers can validate each component independently and have a global view on available components when designing a new view.
Developers don't need to re-implement components on each bounded contexts/projects.

Because components implemented here are used several times they have to be tested unitary and visually.

## How to see components and guidelines?
This project uses Storybook (https://storybook.js.org/) to display components and guidelines.

**Preview**

Latest version is available here: https://akeneo.github.io/akeneo-design-system/

**Locally**

To build this project, you need to have the following package installed:
- Yarn (https://classic.yarnpkg.com/docs/install)

You should execute the following commands:
```bash
$ yarn install
$ yarn storybook:start
```

Then open http://localhost:6006 on your browser.
You don't have to relaunch the command at each time you create or update a component.

## Using Akeneo Design System component in my project

To add Akeneo Design System to your React application, run:
```bash
$ yarn add akeneo-design-system
```

Once the package installed, you should provide the theme related to your project at the top of your application:
```tsx
import React from 'react';
import {ThemeProvider} from 'styled-components';
{/* change with your theme path */}
import {theme} from 'akeneo-design-system/theme/pim'

const App = () => {
  return (
    <>
      <ThemeProvider theme={theme}>
        {/* All your application*/}
      </ThemeProvider>
    </>
  )
};
```

After you can include and render all needed components in your application:
```tsx
import { Badge } from 'akeneo-design-system'

const MyHomePage = () => (
  <div>
    <Badge level="primary">Hello Word!</Badge>
  </div>
)
```

To know props of each component, visit dedicated page to the component here: https://akeneo.github.io/akeneo-design-system/, at the playground section of each component, you can edit all properties and click on "Show code".

## Contribution

This project has been automatically extracted from the following mono-repository: https://github.com/akeneo/pim-community-dev.
If you want to contribute please create a pull request in this repository.

### What should be in the Akeneo Design System?

This repository contains:
- Simple and complex (composition) components used several times in multiple projects/bounded contexts
- Illustrations used several times in multiple projects/bounded contexts
- Icons used by components in this repository
- Basic React hooks
- Specific themes of Akeneo products (PIM, Onboarder, Shared Catalogs)

### What should not be in Akeneo Design System?

- Specific view => each project should keep in charge of create her own view logic
- Code relative to infrastructure (fetchers translation, routing, validation ...)
- Logic specific to the domain

### How to contribute?

We have collected notes on how to contribute to this project in CONTRIBUTING.md.

## Testing instructions

There is two type of tests, unit and visual tests.

### Unit tests

Unit tests can be launched with the following commands:
```batch
# Launch only one time
$ yarn test:unit:run

# Launch in watch mode
$ yarn test:unit:watch
```

Unit test should validate all component behaviors => coverage of 100% is required for component in this project.
Unit tests are in the same directory of the component.

### Visual tests

All components in Storybook are automatically tested visually through snapshot comparison.
Normally, stories should describe all possible states, adding manually visual test should be an exception.

Visual tests cannot be launched on your local computer, they are only launched by the continuous integration because the rendering is dependent on the platform.
When you create a new story or you modify the visual of a component, continuous integration will automatically create and assign to you a pull request.
