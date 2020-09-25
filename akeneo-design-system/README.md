# Akeneo Design System Repository
![Preview](public/preview.png)

## What is a design system?

The aim of Akeneo Design System is to help Akeneo designers and developers deliver work faster and better.
It provides teams with a common language and encourages adherence to design guidelines with compliant components while offering consistent experience through user.

Designer can validate independently each component and have global view on available components when designing new view.
Developers are don't need to re-implement component on each bounded context/projects.

Because component implemented here are used several times they are most strongly tested unitary and visually 

## How to see components and guidelines?

This project use storybook to display components and guidelines. 

**Preview**

Latest version of master is available here: https://akeneo.github.io/akeneo-design-system/

**Locally**

To build this project, should have the following installed package :
- Yarn (https://classic.yarnpkg.com/fr/docs/install)

You should execute the following commands:

```bash
$ yarn install
$ yarn storybook:start
```

Then open http://localhost:6006 on your browser. You don't have to relaunch the command at each change.

## How to contribute?

This project have been automatically extracted from the following mono-repository: https://github.com/akeneo/pim-community-dev.
If you want to contribute please create a pull request in this repository.

### What should be in Akeneo Design System?

This repository contains : 
- Simple and complex (composition) components used several times in multiple projects/bounded contexts
- Illustrations used several times in multiple projects/bounded contexts
- Icons used by components in this repository
- Basic hooks
- Specific themes of Akeneo products (PIM, Onboarder, Shared Catalogs)

### What should not be in Akeneo Design System?

- Specific view => each project should keep in charge of create her own view logic
- Code relative to infrastructure (translation, routing, validation ...)
- Logic specific to the domain

### How to write a component?

**@TODO Maybe move this into the DSM guideline**
- Component should be strongly typed with TypeScript
- Component should be self documented, all props should describe itself through comment.
- Component should be accessible
- Component should not have hardcoded color, it should use color in theme
- Component should use CSS-in-JS
- Simple components should be stateless
- Coding style should follow rules defined in .eslintrc

### How to write a story?

**@TODO Maybe move this into the DSM guideline**

- All stories should be in MDX format (https://mdxjs.com/)
- All properties should editable by the user
- Story should describe when component should be used
- Story should display all possible variations
- Story should follow this template :
```
# Name of the component
Introduction

## Usage
- General guidance
- Functionality

## Playground
- Visual
- Show properties description
- Possibility to interactivelly edit property values

## Variations
- Show all possible variations that allow user to have a better overview of the component capabilities
```

## Testing instructions

There is two type of tests, unit and visual tests.

### Unit tests

Unit tests can be launched with the following commands :
```batch
# Launch only one time
$ yarn test:unit:run 

# Launch in watch mode
$ yarn test:unit:watch
```

Unit test should validate all component behaviors => coverage of 100% is required for component in this project.
Unit tests are in the same directory of the component.

### Visual tests

Visual test can be launched with the following command :
```batch
$ yarn test:visual:run
```

All components in storybook is automatically tested visually through snapshot comparison.
Normally, stories should describe all possible states, adding manually visual test must be an exception.
