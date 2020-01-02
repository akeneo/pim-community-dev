import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';
import * as React from 'react';
import {mount} from 'enzyme';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';

describe('>>>COMPONENT --- Checkbox', () => {
  test('Display a simple Checkbox', () => {
    var value = true;
    const CheckboxView = mount(
      <ThemeProvider theme={akeneoTheme}>
        <Checkbox
          value={value}
          onChange={newValue => {
            value = newValue;
          }}
        />
      </ThemeProvider>
    );

    expect(CheckboxView.find('div[role="checkbox"]').is('[data-checked="true"]')).toEqual(true);
    expect(value).toEqual(true);

    CheckboxView.simulate('keypress', {key: ' '});
    expect(value).toEqual(false);

    CheckboxView.simulate('keypress', {key: ''});
  });

  test('Click on a simple Checkbox', () => {
    var value = true;
    const CheckboxView = mount(
      <ThemeProvider theme={akeneoTheme}>
        <Checkbox
          value={value}
          onChange={newValue => {
            value = newValue;
          }}
        />
      </ThemeProvider>
    );

    expect(value).toEqual(true);

    CheckboxView.simulate('click');
    expect(value).toEqual(false);
  });

  test('Do not trigger if read only', () => {
    var value = true;
    const CheckboxView = mount(
      <ThemeProvider theme={akeneoTheme}>
        <Checkbox
          value={value}
          onChange={newValue => {
            value = newValue;
          }}
          readOnly={true}
        />
      </ThemeProvider>
    );

    expect(CheckboxView.exists('div[role="checkbox"][data-checked="true"]')).toEqual(true);
    expect(value).toEqual(true);

    CheckboxView.simulate('keypress', {key: ' '});
    expect(value).toEqual(true);
  });

  test('Display a simple Checkbox with an id', () => {
    const CheckboxView = mount(
      <ThemeProvider theme={akeneoTheme}>
        <Checkbox value={false} onChange={newValue => {}} id="my_awesome_Checkbox" />
      </ThemeProvider>
    );
    expect(CheckboxView.is('#my_awesome_Checkbox')).toEqual(false);
  });

  test('Display a simple Checkbox in read only', () => {
    var value = true;
    const CheckboxView = mount(
      <ThemeProvider theme={akeneoTheme}>
        <Checkbox value={value} readOnly={true} />
      </ThemeProvider>
    );
    expect(CheckboxView.exists('div[role="checkbox"][data-checked="true"]')).toEqual(true);
    expect(value).toEqual(true);

    CheckboxView.simulate('keypress', {key: ' '});
    CheckboxView.simulate('focus');
    expect(value).toEqual(true);
  });

  test('Non read only Checkbox need an onChange method', () => {
    console.error = jest.fn();

    expect(() => {
      mount(
        <ThemeProvider theme={akeneoTheme}>
          <Checkbox value={true} readOnly={false} />
        </ThemeProvider>
      );
    }).toThrow();

    expect(console.error).toHaveBeenCalled();
  });
});
