import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, getByText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {RuleNotification} from 'akeneoassetmanager/platform/component/rule-notification';

test('It should render the rule notification when the attribute can be updated by a rule', () => {
  const attributeCode = 'packshot';
  const ruleRelations = [
    {
      attribute: 'packshot',
      rule: 'set_packshot',
    },
  ];
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <RuleNotification attributeCode={attributeCode} ruleRelations={ruleRelations} />
    </ThemeProvider>
  );

  expect(getByText('pim_asset_manager.asset_collection.notification.product_rule')).toBeInTheDocument();
  expect(getByText('set_packshot')).toBeInTheDocument();
});

test('It should render multiple rule notifications when the attribute can be updated by a rule', () => {
  const attributeCode = 'packshot';
  const ruleRelations = [
    {
      attribute: 'packshot',
      rule: 'set_packshot',
    },
    {
      attribute: 'packshot',
      rule: 'set_packshot_image',
    },
  ];
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <RuleNotification attributeCode={attributeCode} ruleRelations={ruleRelations} />
    </ThemeProvider>
  );

  expect(getByText('set_packshot, set_packshot_image')).toBeInTheDocument();
});

test('It should not render the rule notification when the attribute can not be updated by a rule', () => {
  const attributeCode = 'another_attribute_asset';
  const ruleRelations = [
    {
      attribute: 'packshot',
      rule: 'set_packshot',
    },
  ];
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <RuleNotification attributeCode={attributeCode} ruleRelations={ruleRelations} />
    </ThemeProvider>
  );

  expect(container).toBeEmpty();
});
