import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {RuleNotification} from 'akeneoassetmanager/platform/component/rule-notification';

jest.mock('pim/security-context', () => ({
  isGranted: () => {
    return true;
  },
}));

const redirect = jest.fn();

jest.mock('@akeneo-pim-community/legacy-bridge/src/hooks/useRouter', () => ({
  useRouter: () => {
    return {
      redirect,
      generate: jest.fn(),
    };
  },
}));

jest.mock('@akeneo-pim-community/legacy-bridge/src/hooks/useTranslate', () => ({
  useTranslate: () => {
    return jest.fn((key: string, params: any, count: number) => {
      switch (key) {
        case 'pimee_enrich.entity.product.module.attribute.can_be_updated_by_rules':
          return 'This attribute can be updated by <span>2 rules</span>';
        default:
          return key;
      }
    });
  },
}));

test('It should render the rule notification when the attribute can be updated by a rule', () => {
  const attributeCode = 'packshot';
  const rulesNumberByAttribute = {packshot: 2};

  renderWithProviders(
    <RuleNotification attributeCode={attributeCode} rulesNumberByAttribute={rulesNumberByAttribute} />
  );

  expect(screen.getByText('This attribute can be updated by')).toBeInTheDocument();

  fireEvent.click(screen.getByText('2 rules'));
  expect(redirect).toHaveBeenCalledTimes(1);
});

test('It should not render the rule notification when the attribute can not be updated by a rule', () => {
  const attributeCode = 'packshot';
  const rulesNumberByAttribute = {};
  const {container} = renderWithProviders(
    <RuleNotification attributeCode={attributeCode} rulesNumberByAttribute={rulesNumberByAttribute} />
  );

  expect(container).toBeEmptyDOMElement();
});
