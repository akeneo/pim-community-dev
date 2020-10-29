import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  fireEvent,
  renderWithProviders,
  screen,
} from '../../../../../../../test-utils';
import {createAttribute} from '../../../../../factories';
import {AttributeValue} from '../../../../../../../src/pages/EditRules/components/actions/attribute';
import {
  AttributeType,
  getAttributeLabel,
} from '../../../../../../../src/models';

describe('AttributeValue', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display an information text with a non selected attribute', () => {
    renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        name={'attribute-value-name'}
        value={'default'}
        onChange={jest.fn()}
        actionType={'set'}
      />,
      {all: true}
    );
    expect(
      screen.getByText('pimee_catalog_rule.form.edit.please_select_attribute')
    ).toBeInTheDocument();
    expect(screen.queryByTestId('attribute-value-id')).not.toBeInTheDocument();
  });

  it('should display a disabled value with an unknown attribute', () => {
    renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={null}
        name={'attribute-value-name'}
        value={'default'}
        onChange={jest.fn()}
        actionType={'set'}
      />,
      {all: true}
    );
    const valueInput = screen.getByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('default');
    expect(valueInput).toBeDisabled();
    expect(valueInput).toHaveProperty('type', 'text');
  });

  it('should display a disabled value with a non managed attribute', () => {
    const attribute = createAttribute({type: 'unknown'});
    renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        value={'default'}
        onChange={jest.fn()}
        actionType={'set'}
      />,
      {all: true}
    );

    expect(
      screen.getByText('pimee_catalog_rule.form.edit.unhandled_attribute_type')
    ).toBeInTheDocument();
    const valueInput = screen.getByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('default');
    expect(valueInput).toBeDisabled();
    expect(valueInput).toHaveProperty('type', 'text');
  });

  it('should display a text value with a text attribute', () => {
    const attribute = createAttribute({});
    renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        value={'default'}
        onChange={jest.fn()}
        actionType={'set'}
      />,
      {all: true}
    );
    expect(
      screen.getByText(
        `${getAttributeLabel(attribute, 'en_US')} pim_common.required_label`
      )
    ).toBeInTheDocument();
    const valueInput = screen.getByTestId('attribute-value-id');
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

    const attribute = createAttribute({type: 'pim_catalog_simpleselect'});
    const onChange = jest.fn();
    renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        value={'test2'}
        onChange={onChange}
        actionType={'set'}
      />,
      {all: true}
    );

    expect(
      screen.getByText(
        `${getAttributeLabel(attribute, 'en_US')} pim_common.required_label`
      )
    ).toBeInTheDocument();
    const valueInput = await screen.findByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('test2');
    expect(valueInput).not.toBeDisabled();
    act(() => {
      fireEvent.change(valueInput, {
        target: {value: 'test1'},
      });
      expect(onChange).toHaveBeenCalledTimes(1);
    });
  });

  it('should display a date input', async () => {
    const attribute = createAttribute({
      code: 'release_date',
      type: 'pim_catalog_date',
      scopable: false,
      localizable: false,
      labels: {
        en_US: 'Release date',
      },
    });
    const onChange = jest.fn();
    renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        onChange={onChange}
        actionType={'set'}
      />,
      {all: true}
    );

    expect(
      screen.getByText('Release date pim_common.required_label')
    ).toBeInTheDocument();
    const valueInput = await screen.findByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('');
    expect(valueInput).not.toBeDisabled();
    act(() => {
      fireEvent.change(valueInput, {
        target: {value: '2020-05-20'},
      });
      expect(onChange).toHaveBeenCalledTimes(1);
    });
  });

  it('should display a text area (without wysiwyg', () => {
    const attribute = createAttribute({
      type: AttributeType.TEXTAREA,
      wysiwyg_enabled: false,
    });
    renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        value={'default'}
        onChange={jest.fn()}
        actionType={'set'}
      />,
      {all: true}
    );
    expect(
      screen.getByText(
        `${getAttributeLabel(attribute, 'en_US')} pim_common.required_label`
      )
    ).toBeInTheDocument();
    const valueInput = screen.getByTestId('attribute-value-id');
    expect(valueInput).toHaveValue('default');
    expect(valueInput).not.toBeDisabled();
    expect(valueInput).toHaveProperty('type', 'textarea');
  });

  it('should display a text area with wysiwyg', () => {
    const attribute = createAttribute({
      type: AttributeType.TEXTAREA,
      wysiwyg_enabled: true,
    });
    renderWithProviders(
      <AttributeValue
        id={'attribute-value-id'}
        attribute={attribute}
        name={'attribute-value-name'}
        value={'default'}
        onChange={jest.fn()}
        actionType={'set'}
      />,
      {all: true}
    );
    expect(
      screen.getByText(
        `${getAttributeLabel(attribute, 'en_US')} pim_common.required_label`
      )
    ).toBeInTheDocument();
    // Wysiwyg editor container looks like <div aria-label="rdw-wrapper" className="rdw-editor-wrapper"...>
    const valueInputs = screen.getByLabelText('rdw-wrapper');
    expect(valueInputs).toBeInTheDocument();
  });
});
