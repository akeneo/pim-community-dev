import Checkbox from 'akeneoreferenceentity/application/component/app/checkbox';
import * as React from 'react';
import {mount} from 'enzyme';

describe('>>>COMPONENT --- Checkbox', () => {
  test('Display a simple Checkbox', () => {
    var value = true;
    const CheckboxView = mount(
      <Checkbox
        value={value}
        onChange={newValue => {
          value = newValue;
        }}
      />
    );

    expect(CheckboxView.find('.AknCheckbox').is('[aria-checked="true"]')).toEqual(true);
    expect(CheckboxView.find('.AknCheckbox').is('[data-checked="true"]')).toEqual(true);
    expect(value).toEqual(true);

    CheckboxView.simulate('keypress', {key: ' '});
    expect(value).toEqual(false);

    CheckboxView.simulate('keypress', {key: ''});
  });

  test('Click on a simple Checkbox', () => {
    var value = true;
    const CheckboxView = mount(
      <Checkbox
        value={value}
        onChange={newValue => {
          value = newValue;
        }}
      />
    );

    expect(value).toEqual(true);

    CheckboxView.simulate('click');
    expect(value).toEqual(false);
  });

  test('Do not trigger if read only', () => {
    var value = true;
    const CheckboxView = mount(
      <Checkbox
        value={value}
        onChange={newValue => {
          value = newValue;
        }}
        readOnly={true}
      />
    );

    expect(CheckboxView.find('.AknCheckbox').is('[aria-checked="true"]')).toEqual(true);
    expect(CheckboxView.find('.AknCheckbox').is('[data-checked="true"]')).toEqual(true);
    expect(value).toEqual(true);

    CheckboxView.simulate('keypress', {key: ' '});
    expect(value).toEqual(true);
  });

  test('Display a simple Checkbox with an id', () => {
    const CheckboxView = mount(<Checkbox value={false} onChange={newValue => {}} id="my_awesome_Checkbox" />);
    expect(CheckboxView.is('#my_awesome_Checkbox')).toEqual(true);
  });

  test('Display a simple Checkbox in read only', () => {
    var value = true;
    const CheckboxView = mount(<Checkbox value={value} readOnly={true} />);

    expect(CheckboxView.find('.AknCheckbox').is('[aria-checked="true"]')).toEqual(true);
    expect(CheckboxView.find('.AknCheckbox').is('[data-checked="true"]')).toEqual(true);
    expect(value).toEqual(true);

    CheckboxView.simulate('keypress', {key: ' '});
    expect(value).toEqual(true);
  });

  test('Non read only Checkbox need an onChange method', () => {
    console.error = jest.fn();

    expect(() => {
      mount(<Checkbox value={true} readOnly={false} />);
    }).toThrow();

    expect(console.error).toHaveBeenCalled();
  });
});
