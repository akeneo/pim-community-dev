import Switch from 'akeneoreferenceentity/application/component/app/switch';
import * as React from 'react';
import {mount} from 'enzyme';

describe('>>>COMPONENT --- switch', () => {
  test('Display a simple switch', () => {
    var value = true;
    const switchView = mount(
      <Switch
        value={value}
        onChange={newValue => {
          value = newValue;
        }}
      />
    );

    expect(switchView.find('.AknSwitch').is('[aria-checked="true"]')).toEqual(true);
    expect(switchView.find('.AknSwitch-input').is('[checked]')).toEqual(true);
    expect(value).toEqual(true);

    switchView.find('input').simulate('change', {target: {checked: false}});
    expect(value).toEqual(false);

    switchView.find('label').simulate('keypress', {key: ' '});
    switchView.find('label').simulate('keypress', {key: ''});
  });

  test('Do not trigger if read only', () => {
    var value = true;
    const switchView = mount(
      <Switch
        value={value}
        onChange={newValue => {
          value = newValue;
        }}
      />
    );

    expect(switchView.find('.AknSwitch').is('[aria-checked="true"]')).toEqual(true);
    expect(switchView.find('.AknSwitch-input').is('[checked]')).toEqual(true);
    expect(value).toEqual(true);

    switchView.find('input').simulate('change', {target: undefined});
    expect(value).toEqual(true);
  });

  test('Display a simple switch with an id', () => {
    const switchView = mount(<Switch value={false} onChange={newValue => {}} id="my_awesome_switch" />);
    expect(switchView.is('#my_awesome_switch')).toEqual(true);
  });

  test('Display a simple switch in read only', () => {
    var value = true;
    const switchView = mount(<Switch value={value} readOnly={true} />);

    expect(switchView.find('.AknSwitch').is('[aria-checked="true"]')).toEqual(true);
    expect(switchView.find('.AknSwitch-input').is('[checked]')).toEqual(true);
    expect(value).toEqual(true);

    switchView.find('input').simulate('change', {target: {checked: false}});
    expect(value).toEqual(true);
  });

  test('Non read only switch need an onChange method', () => {
    console.error = jest.fn();

    expect(() => {
      mount(<Switch value={true} readOnly={false} />);
    }).toThrow();

    expect(console.error).toHaveBeenCalled();
  });
});
