import {mount} from 'enzyme';
import * as React from 'react';

import {
  ActionButton,
  Button,
  GhostButton
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/component/app/buttons';

describe('Application > Component > App > Button', () => {
  test('it renders the button', () => {
    const label = 'test';
    const button = mount(<Button onClick={() => {}}>{label}</Button>);

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').text()).toEqual('test');
  });

  test('it renders the button with additional classname', () => {
    const className = 'test--class';
    const label = 'test';
    const button = mount(
      <Button classNames={[className]} onClick={() => {}}>
        {label}
      </Button>
    );

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass(className)).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').text()).toEqual('test');
  });

  test('it renders the button with count value > 0', () => {
    const className = 'test--class';
    const label = 'test';
    const count = 10;
    const button = mount(
      <Button classNames={[className]} count={count} onClick={() => {}}>
        {label}
      </Button>
    );

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass(className)).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').text()).toEqual(`${label}${count}`);
  });

  test('it disables the button when the count props equals to 0', () => {
    const className = 'test--class';
    const label = 'test';
    const count = 0;
    const button = mount(
      <Button classNames={[className]} count={count} onClick={() => {}}>
        {label}
      </Button>
    );

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass(className)).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(true);
    expect(button.find('button').text()).toEqual(`${label}${count}`);
  });

  test('it disables the button with counter when the disabled property is defined', () => {
    const className = 'test--class';
    const label = 'test';
    const count = 10;
    const disabled = true;
    const button = mount(
      <Button classNames={[className]} count={count} onClick={() => {}} disabled={disabled}>
        {label}
      </Button>
    );

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass(className)).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(true);
    expect(button.find('button').text()).toEqual(`${label}${count}`);
  });
});

describe('Application > Component > App > ActionButton', () => {
  test('it renders the button', () => {
    const label = 'test';
    const button = mount(<ActionButton onClick={() => {}}>{label}</ActionButton>);

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').hasClass('AknButton--action')).toEqual(true);
    expect(button.find('button').text()).toEqual('test');
  });

  test('it renders the button with additional classname', () => {
    const className = 'test--class';
    const label = 'test';
    const button = mount(
      <ActionButton classNames={[className]} onClick={() => {}}>
        {label}
      </ActionButton>
    );

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass(className)).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').hasClass('AknButton--action')).toEqual(true);
    expect(button.find('button').text()).toEqual('test');
  });

  test('it renders the button with counter', () => {
    const className = 'test--class';
    const label = 'test';
    const count = 10;
    const button = mount(
      <ActionButton classNames={[className]} count={count} onClick={() => {}}>
        {label}
      </ActionButton>
    );

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass(className)).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').hasClass('AknButton--action')).toEqual(true);
    expect(button.find('button').text()).toEqual(`${label}${count}`);
  });
});

describe('Application > Component > App > GhostButton', () => {
  test('it renders the button', () => {
    const label = 'test';
    const button = mount(<GhostButton onClick={() => {}}>{label}</GhostButton>);

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').hasClass('AknButton--ghost')).toEqual(true);
    expect(button.find('button').text()).toEqual('test');
  });
  test('it renders the button with additional classname', () => {
    const className = 'test--class';
    const label = 'test';
    const button = mount(
      <GhostButton classNames={[className]} onClick={() => {}}>
        {label}
      </GhostButton>
    );

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass(className)).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').hasClass('AknButton--ghost')).toEqual(true);
    expect(button.find('button').text()).toEqual('test');
  });

  test('it renders the button with counter', () => {
    const className = 'test--class';
    const label = 'test';
    const count = 10;
    const button = mount(
      <GhostButton classNames={[className]} count={count} onClick={() => {}}>
        {label}
      </GhostButton>
    );

    expect(button.find('button').is('[disabled]')).toEqual(true);
    expect(button.find('button').hasClass(className)).toEqual(true);
    expect(button.find('button').hasClass('AknButton--disabled')).toEqual(false);
    expect(button.find('button').hasClass('AknButton--ghost')).toEqual(true);
    expect(button.find('button').text()).toEqual(`${label}${count}`);
  });
});
