import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {ValidationErrorCollection} from 'akeneoassetmanager/platform/component/common/validation-error-collection';

test('It should render the attribute error validation messages for the current context', () => {
  const attributeCode = 'packshot';
  const context = {
    channel: 'ecommerce',
    locale: 'en_US',
  };
  const errors = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'Wrong packshot',
      channel: 'ecommerce',
    },
  ];

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ValidationErrorCollection attributeCode={attributeCode} context={context} errors={errors} />
    </ThemeProvider>
  );

  expect(getByText('Wrong packshot')).toBeInTheDocument();
});

test('It should render different attribute error validation messages', () => {
  const attributeCode = 'packshot';
  const context = {
    channel: 'ecommerce',
    locale: 'en_US',
  };
  const errors = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'Wrong packshot',
      channel: 'ecommerce',
    },
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'Not a valid value',
      channel: 'ecommerce',
    },
  ];

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ValidationErrorCollection attributeCode={attributeCode} context={context} errors={errors} />
    </ThemeProvider>
  );

  expect(getByText('Wrong packshot')).toBeInTheDocument();
  expect(getByText('Not a valid value')).toBeInTheDocument();
});

test('It should not render the attribute error validation messages when it does not have errors for the current context', () => {
  const attributeCode = 'packshot';
  const context = {
    channel: 'mobile',
    locale: 'fr_FR',
  };
  const errors = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'Wrong packshot',
      channel: 'ecommerce',
    },
  ];

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ValidationErrorCollection attributeCode={attributeCode} context={context} errors={errors} />
    </ThemeProvider>
  );

  expect(container).toBeEmpty();
});

test('It should not render the attribute error validation messages when the attribute does not have errors', () => {
  const attributeCode = 'another_asset_attribute';
  const context = {
    channel: 'ecommerce',
    locale: 'en_US',
  };
  const errors = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'Wrong packshot',
      channel: 'ecommerce',
    },
  ];

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ValidationErrorCollection attributeCode={attributeCode} context={context} errors={errors} />
    </ThemeProvider>
  );

  expect(container).toBeEmpty();
});
