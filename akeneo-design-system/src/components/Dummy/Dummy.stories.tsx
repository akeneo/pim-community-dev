import React from 'react';
import {Story, Meta} from '@storybook/react/types-6-0';
import {withKnobs, select} from '@storybook/addon-knobs';
import {action} from '@storybook/addon-actions';
import {Dummy, DummyProps} from './Dummy';

export default {
  title: 'Components/Dummy',
  component: Dummy,
  argTypes: {onClick: {action: 'Dummy component clicked'}},
} as Meta;

const Template: Story<DummyProps> = args => <Dummy {...args} />;

const Primary = Template.bind({});
Primary.args = {};

const LargeButton = Template.bind({});
LargeButton.args = {
  size: 48,
};

const Secondary = () => <Dummy onClick={action('Secondary clicked')} size={select('size', [12, 24, 48], 12)} />;
Secondary.story = {
  decorators: [withKnobs],
};

export {Primary, Secondary};
