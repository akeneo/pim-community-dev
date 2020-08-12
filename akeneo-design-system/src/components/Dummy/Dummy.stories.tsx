import React from 'react';
import {Story, Meta} from '@storybook/react/types-6-0';
import {Dummy, DummyProps} from './Dummy';

export default {
  title: 'Components/Dummy',
  component: Dummy,
  argTypes: {
    onClick: {action: 'Dummy component clicked'},
    type: {control: {type: 'select', options: ['Primary', 'Secondary']}}
  },
} as Meta;

const Template: Story<DummyProps> = args => <Dummy {...args} />;

const Primary = Template.bind({});
Primary.args = {
  size: 24
};
Primary.argTypes = {
  size: {control: { type: 'select', options: [12, 24, 48] }},
}

const LargeButton = Template.bind({});
LargeButton.args = {
  size: 48,
};

const Secondary = Template.bind({});
Secondary.args = {};

export {Primary, Secondary};
