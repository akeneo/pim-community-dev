import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  fireEvent,
  renderWithProviders,
} from '../../../../../../../test-utils';
import { createAttribute } from '../../../../../factories';
import { AttributeValue } from '../../../../../../../src/pages/EditRules/components/actions/attribute';
import { getAttributeLabel } from '../../../../../../../src/models';
import userEvent from '@testing-library/user-event';

jest.mock('../../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock(
  '../../../../../../../src/dependenciesTools/provider/dependencies.ts'
);
jest.mock('../../../../../../../src/fetch/categoryTree.fetcher.ts');

describe('AttributeValue', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display an information text with a non selected attribute', async () => {
    const { findByText, queryByTestId } = renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        name={'attribute-value-name'}
        value={'default'}
      />,
      { all: true }
    );

    expect(
      await findByText('pimee_catalog_rule.form.edit.please_select_attribute')
    ).toBeInTheDocument();
    const valueInput = queryByTestId('attribute-value-id');
    expect(valueInput).not.toBeInTheDocument();
  });

  it('should display a disabled value with an unknown attribute', async () => {
    const { findByTestId } = renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={null}
        name={'attribute-value-name'}
        value={'default'}
      />,
      { all: true }
    );

    const valueInput = await findByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('default');
    expect(valueInput).toBeDisabled();
    expect(valueInput).toHaveProperty('type', 'text');
  });

  it('should display a disabled value with a non managed attribute', async () => {
    const attribute = createAttribute({ type: 'unknown' });
    const { findByText, findByTestId } = renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        value={'default'}
      />,
      { all: true }
    );

    expect(
      await findByText('pimee_catalog_rule.form.edit.unhandled_attribute_type')
    ).toBeInTheDocument();
    const valueInput = await findByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('default');
    expect(valueInput).toBeDisabled();
    expect(valueInput).toHaveProperty('type', 'text');
  });

  it('should display a text value with a text attribute', async () => {
    const attribute = createAttribute({});
    const { findByText, findByTestId } = renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        value={'default'}
      />,
      { all: true }
    );

    expect(
      await findByText(getAttributeLabel(attribute, 'en_US'))
    ).toBeInTheDocument();
    const valueInput = await findByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('default');
    expect(valueInput).not.toBeDisabled();
    expect(valueInput).toHaveProperty('type', 'text');
  });

  it('should display a select value with a simple select attribute and we can change the value', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_ui_ajaxentity_list')) {
        return Promise.resolve(
          JSON.stringify([
            {
              id: 'test1',
              text: 'Test 1',
            },
            {
              id: 'test2',
              text: 'Test 2',
            },
          ])
        );
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const attribute = createAttribute({ type: 'pim_catalog_simpleselect' });
    const { findByText, findByTestId } = renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        value={'test2'}
      />,
      { all: true }
    );

    expect(
      await findByText(getAttributeLabel(attribute, 'en_US'))
    ).toBeInTheDocument();
    const valueInput = await findByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('test2');
    expect(valueInput).not.toBeDisabled();

    await act(async () => {
      userEvent.click(await findByTestId('attribute-value-id'));
      expect(
        (await findByTestId('attribute-value-id')).children.length
      ).toBeGreaterThan(1);
      fireEvent.change(await findByTestId('attribute-value-id'), {
        target: { value: 'test1' },
      });
    });
    expect(valueInput).toHaveValue('test1');
  });
});
