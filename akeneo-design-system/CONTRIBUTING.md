# Contributing

As this design system will be used among multiple products and multiple teams, we prefer consistency above all. So the best way to know how to do something new is to look around.

## Adding a new component

Before writing a component you must know what problem it is supposed to solve and in what context to use it.
Before adding a new component, please see if the component does not already exist, or the problem is not already solved by another component.

- Create a branch (`git checkout -b branch_name`)
- Create a new file related to your component `src/components/ComponentName/ComponentName.tsx`
  - First of all define all properties that your component needs to work, then use typescript types to type them the most strongly possible
  - Add comment on all your properties (those comments will be displayed in Storybook)
  - Write related logic of the component
  - Add style in your component with [styled components](https://styled-components.com/docs).
  - Add (if necessary) icons in the folder `src/icons/` (color, height and weight should be configurable)
  - Add forwardRef management on your component (https://reactjs.org/docs/forwarding-refs.html)
  - Forward ...props to your component
  - Add accessibility in your component (https://developer.mozilla.org/en-US/docs/Web/Accessibility)
- Create a new file related to the stories of your component `src/components/ComponentName/ComponentName.stories.tsx`
  - Get the template from [Story template](#story-template) and replace `ComponentName` by your component name
  - Write `Usage introduction` and `General guidance` paragraph
  - Define all `argTypes`
    - Use `control` when your property expects a value (string, number, boolean, enum ...), you can retrieve full list of control types here: https://storybook.js.org/docs/react/essentials/controls#annotation.
    - Use `action` when your property expects a callback relative to an action on the component
  - Define `arg` when you want a specific default value
  - Write all variations of your component
- Create a new file src/components/`ComponentName/ComponentName.unit.tsx`
  - Add unit tests to validate all component behaviours
- Commit and push your change (Github Action will be automatically launched)
- Github Action will assign you a pull request to alert you that new stories found, review it and merge it when it looks good for you
- Github Action will deploy a new version of the storybook give the url to designer for review

## Update style of component

If you update the style of one component, the visual test will fail. Github Action will assign you a pull request to alert you that component changed, review it and merge it when it looks good for you.

Github Action will deploy a new version of the storybook give the url to designer for review.

## Components guidelines

- Component should be strongly typed with TypeScript
- All properties should be documented
- Simple components should be stateless
- Coding style should follow rules defined in .eslintrc
- Component should manage forwardRef and ...props
- Component should provide aria attributes if necessary
- Components should be keyboard accessible
- Component should not have hardcoded color, it should use color in theme
- Component should use CSS-in-JS with [styled components](https://styled-components.com/docs)
- Components should follow the [compound components pattern](https://www.youtube.com/watch?v=hEGg-3pIHlE).

## Stories guidelines

- All stories should be in [MDX format](https://mdxjs.com/)
- Story should describe when the component should be used
- All properties should be editable by the user
- Story should display all possible variations
- Variations should describe itself through value label when it's possible

## Story template

```mdx
import {Meta, Story, ArgsTable, Canvas} from '@storybook/addon-docs/blocks';
import {ComponentName} from './ComponentName';

<Meta
  title="Components/ComponentName"
  component={ComponentName}
  argTypes={{
    arg1: {control: {type: 'select', options: ['default', 'small']}},
    arg2: {control: {type: 'boolean'}},
    onArg3: {action: 'Click on the button'},
  }}
  args={{
    arg1: 'default',
  }}
/>

# Name of the component

## Usage

_Describe what problem the component is supposed to solve_

### General guidance

_Describe when the component should be used_
_Describe each element on the component_

## Playground

<Canvas>
  <Story name="Standard">
    {args => {
      return <ComponentName {...args} />;
    }}
  </Story>
</Canvas>

## Variation through arg1

_Show all possible variations that allow user to have a better overview of the component capabilities_

<Canvas>
  <Story name="Variation through arg1">
    {args => {
      return (
        <>
          <ComponentName {...args} arg1="default">
            Default
          </ComponentName>
          <ComponentName {...args} arg1="small">
            Small
          </ComponentName>
        </>
      );
    }}
  </Story>
</Canvas>

## Variation through arg2

<Canvas>
  <Story name="Variation through arg2">
    {args => {
      return (
        <>
          <ComponentName {...args} arg2={false}>
            Default
          </ComponentName>
          <ComponentName {...args} arg2={true}>
            Arg2 activated
          </ComponentName>
        </>
      );
    }}
  </Story>
</Canvas>
```

## Tests guidelines

- Test should use [Jest](https://jestjs.io/docs/en/getting-started)
- Test should use [React Testing Library](https://testing-library.com/docs/react-testing-library/intro)
