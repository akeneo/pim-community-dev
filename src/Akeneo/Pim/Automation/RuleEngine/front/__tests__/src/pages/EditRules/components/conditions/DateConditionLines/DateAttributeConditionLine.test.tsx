import React from 'react';
import userEvent from '@testing-library/user-event';
import {DateAttributeConditionLine} from '../../../../../../../src/pages/EditRules/components/conditions/DateConditionLines';
import {createAttribute, locales, scopes} from '../../../../../factories';
import {Operator} from '../../../../../../../src/models/Operator';
import {render, screen, act} from '../../../../../../../test-utils';
import {DateTypeOptionIds} from '../../../../../../../src/pages/EditRules/components/conditions/DateConditionLines/dateConditionLines.type';

describe('DateAttributeConditionLine', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });
  it('should render by default with scope and locale and no value', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: true, scopable: true})),
      {status: 200}
    );

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale={'en_US'}
      />,
      {all: true}
    );
    // When
    const operator = await screen.findByTestId(
      'content.conditions[0].operator'
    );

    expect(
      screen.getByText('pimee_catalog_rule.form.date.label.scope')
    ).toBeInTheDocument();
    const scope = screen.getByTestId('content.conditions[0].scope');

    expect(
      screen.getByText('pimee_catalog_rule.form.date.label.locale')
    ).toBeInTheDocument();
    const locale = screen.getByTestId('content.conditions[0].locale');
    // Then
    expect(operator).toHaveValue('EMPTY');
    expect(locale).toHaveValue('');
    expect(scope).toHaveValue('');
  });
  it('should render with condition registered in react hook form (no scope, no locale)', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: '2020-05-11',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale={'en_US'}
      />,
      {all: true},
      {toRegister, defaultValues}
    );
    // When
    const operator = await screen.findByTestId(
      'content.conditions[0].operator'
    );

    expect(
      screen.getByText('pimee_catalog_rule.form.date.label.date_type')
    ).toBeInTheDocument();
    const dateType = screen.getByTestId('date-type-0');

    // Then
    expect(operator).toHaveValue('=');
    expect(dateType).toHaveValue('SPECIFIC_DATE');
    expect(
      screen.getByLabelText('pimee_catalog_rule.form.date.label.date')
    ).toHaveValue('2020-05-11');
  });
  it('should hide the value input when operator is empty or not empty ', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: '2020-05-11',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true},
      {toRegister, defaultValues}
    );
    // When
    const operator = await screen.findByTestId(
      'content.conditions[0].operator'
    );
    expect(operator).toHaveValue('=');
    expect(
      screen.getByText('pimee_catalog_rule.form.date.label.date')
    ).toBeInTheDocument();

    // Then
    act(() => {
      userEvent.selectOptions(operator, Operator.IS_NOT_EMPTY);
    });
    expect(operator).toHaveValue('NOT EMPTY');
    expect(
      screen.queryByText('pimee_catalog_rule.form.date.label.date')
    ).toBeNull();
    act(() => {
      userEvent.selectOptions(operator, Operator.IS_EMPTY);
    });
    expect(operator).toHaveValue('EMPTY');
    expect(
      screen.queryByText('pimee_catalog_rule.form.date.label.date')
    ).toBeNull();
  });
  it('should show an input date when specific date is selected', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: '2020-05-11',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true},
      {toRegister, defaultValues}
    );

    // When
    expect(
      await screen.findByTestId('content.conditions[0].operator')
    ).toHaveValue('=');

    // Then
    const inputDate = screen.getByLabelText(
      'pimee_catalog_rule.form.date.label.date'
    );
    expect(inputDate).toBeInTheDocument();
    expect(inputDate).toHaveValue('2020-05-11');
  });
  it('should show a relative date input when date type is date in the future or past is selected', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true}
    );

    // When
    const operator = await screen.findByTestId(
      'content.conditions[0].operator'
    );
    act(() => {
      userEvent.selectOptions(operator, Operator.EQUALS);
    });
    const dateTypeSelect = screen.getByTestId('date-type-0');
    act(() => {
      userEvent.selectOptions(dateTypeSelect, DateTypeOptionIds.FUTURE_DATE);
    });

    // Then
    const inputDate = screen.getByLabelText(
      'pimee_catalog_rule.form.date.label.relative_date'
    );
    expect(inputDate).toBeInTheDocument();
    expect(inputDate).toHaveValue('0');
    expect(
      screen.getAllByText('pimee_catalog_rule.form.date.day')
    ).toHaveLength(2); // Selected and options
    expect(
      screen.getByText('pimee_catalog_rule.form.date.week')
    ).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.date.month')
    ).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.date.year')
    ).toBeInTheDocument();

    // After change to PAST_DATE we are keeping the values from future date
    act(() => {
      userEvent.selectOptions(dateTypeSelect, DateTypeOptionIds.PAST_DATE);
    });
    expect(dateTypeSelect).toHaveValue(DateTypeOptionIds.PAST_DATE);
    expect(inputDate).toBeInTheDocument();
    expect(inputDate).toHaveValue('0');
    expect(
      screen.getAllByText('pimee_catalog_rule.form.date.day')
    ).toHaveLength(2); // Selected and options
  });
  it('should show a relative future date input when value has a + sign', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: '+1 day',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true},
      {toRegister, defaultValues}
    );

    // When
    const dateTypeSelect = await screen.findByTestId('date-type-0');

    // Then
    expect(dateTypeSelect).toHaveValue(DateTypeOptionIds.FUTURE_DATE);
  });
  it('should show a relative past date input when value has a - sign', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: '-1 day',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true},
      {toRegister, defaultValues}
    );

    // When
    const dateTypeSelect = await screen.findByTestId('date-type-0');

    // Then
    expect(dateTypeSelect).toHaveValue(DateTypeOptionIds.PAST_DATE);
  });
  it('should change the time period when I click on a new one', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: '-1 day',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true},
      {toRegister, defaultValues}
    );

    // When
    expect(await screen.findByTestId('date-type-0')).toHaveValue(
      DateTypeOptionIds.PAST_DATE
    );

    // Then
    expect(
      screen.getAllByText('pimee_catalog_rule.form.date.day')
    ).toHaveLength(2); // Selected and options
    userEvent.click(screen.getByText('pimee_catalog_rule.form.date.month'));
    expect(
      screen.getAllByText('pimee_catalog_rule.form.date.month')
    ).toHaveLength(2); // Selected and options
  });
  it('should display the present date type with no input when value is now', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: 'now',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true},
      {toRegister, defaultValues}
    );

    // When
    expect(await screen.findByTestId('date-type-0')).toHaveValue(
      DateTypeOptionIds.PRESENT
    );

    // Then
    expect(
      screen.queryByLabelText(
        'pimee_catalog_rule.form.date.label.relative_date'
      )
    ).toBe(null);
    expect(
      screen.queryByLabelText('pimee_catalog_rule.form.date.label.date')
    ).toBe(null);
  });
  it('should have no specific date and two date input when operator is between', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true}
    );

    // When
    const operator = await screen.findByTestId(
      'content.conditions[0].operator'
    );
    act(() => {
      userEvent.selectOptions(operator, Operator.BETWEEN);
    });
    // Then
    expect(operator).toHaveValue(Operator.BETWEEN);
    expect(
      screen.getByLabelText('pimee_catalog_rule.form.date.label.date_from')
    ).toBeInTheDocument();
    expect(
      screen.getByLabelText('pimee_catalog_rule.form.date.label.date_to')
    ).toBeInTheDocument();
  });
  it('should have no specific date and two date input when operator is not between', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true}
    );

    // When
    const operator = await screen.findByTestId(
      'content.conditions[0].operator'
    );
    act(() => {
      userEvent.selectOptions(operator, Operator.NOT_BETWEEN);
    });
    // Then
    expect(operator).toHaveValue(Operator.NOT_BETWEEN);
    expect(
      screen.getByLabelText('pimee_catalog_rule.form.date.label.date_from')
    ).toBeInTheDocument();
    expect(
      screen.getByLabelText('pimee_catalog_rule.form.date.label.date_to')
    ).toBeInTheDocument();
  });
  it('should display a in between operator when value is an array of 2 dates', async () => {
    // Given
    // Mock the call to getAttributeByIdentifier in the component
    fetchMock.mockResponse(
      JSON.stringify(createAttribute({localizable: false, scopable: false})),
      {status: 200}
    );

    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.BETWEEN,
            value: ['2020-10-01', '2021-02-18'],
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateAttributeConditionLine
        condition={{
          field: 'my_date',
          operator: Operator.IS_EMPTY,
          value: '',
        }}
        locales={locales}
        scopes={scopes}
        lineNumber={0}
        currentCatalogLocale='en_US'
      />,
      {all: true},
      {toRegister, defaultValues}
    );

    // Then
    expect(
      await screen.findByLabelText(
        'pimee_catalog_rule.form.date.label.date_from'
      )
    ).toHaveValue('2020-10-01');
    expect(
      screen.getByLabelText('pimee_catalog_rule.form.date.label.date_to')
    ).toHaveValue('2021-02-18');
  });
});
