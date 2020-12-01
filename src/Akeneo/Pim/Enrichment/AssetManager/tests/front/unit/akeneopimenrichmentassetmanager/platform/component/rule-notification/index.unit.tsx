import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {RuleNotification} from 'akeneoassetmanager/platform/component/rule-notification';

test('It should render the rule notification when the attribute can be updated by a rule', () => {
  const attributeCode = 'packshot';
  const ruleRelations = [
    {
      attribute: 'packshot',
      rule: 'set_packshot',
    },
  ];
  renderWithProviders(<RuleNotification attributeCode={attributeCode} ruleRelations={ruleRelations} />);

  expect(screen.getByText('pim_asset_manager.asset_collection.notification.product_rule')).toBeInTheDocument();
  expect(screen.getByText('set_packshot')).toBeInTheDocument();
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
  renderWithProviders(<RuleNotification attributeCode={attributeCode} ruleRelations={ruleRelations} />);

  expect(screen.getByText('set_packshot, set_packshot_image')).toBeInTheDocument();
});

test('It should not render the rule notification when the attribute can not be updated by a rule', () => {
  const attributeCode = 'another_attribute_asset';
  const ruleRelations = [
    {
      attribute: 'packshot',
      rule: 'set_packshot',
    },
  ];
  const {container} = renderWithProviders(
    <RuleNotification attributeCode={attributeCode} ruleRelations={ruleRelations} />
  );

  expect(container).toBeEmptyDOMElement();
});
