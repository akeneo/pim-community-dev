import React from 'react';
import userEvent from '@testing-library/user-event';
import {DateSystemConditionLine} from '../../../../../../../src/pages/EditRules/components/conditions/DateConditionLines';
import {Operator} from '../../../../../../../src/models/Operator';
import {render, screen, act} from '../../../../../../../test-utils';
import {DateTypeOptionIds} from '../../../../../../../src/pages/EditRules/components/conditions/DateConditionLines/dateConditionLines.type';

describe('DateSystemConditionLine', () => {
  it('should render by default', async () => {
    // Given
    render(
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
      />,
      {all: true}
    );
    // When
    const operator = await screen.findByTestId(
      'content.conditions[0].operator'
    );
    // Then
    expect(operator).toHaveValue('=');
    expect(
      screen.getByLabelText('pimee_catalog_rule.form.date.label.date')
    ).toHaveValue('');
  });
  it('should render with condition registered in react hook form', async () => {
    // Given
    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: '2020-05-11T18:53',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
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
    expect(operator).toHaveValue('=');
    expect(dateType).toHaveValue('SPECIFIC_DATE');

    // Then
    const inputDateTime = screen.getByTestId(
      'date-input-0'
    ) as HTMLInputElement;
    expect(inputDateTime.getAttribute('value')).toEqual('2020-05-11T18:53');
  });
  it('should show an input date time local when specific date is selected', async () => {
    // Given
    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: '2020-05-11T18:53',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
      />,
      {all: true},
      {toRegister, defaultValues}
    );

    // When
    expect(
      await screen.findByTestId('content.conditions[0].operator')
    ).toHaveValue('=');

    // Then
    const inputDateTime = screen.getByTestId(
      'date-input-0'
    ) as HTMLInputElement;
    expect(inputDateTime.getAttribute('value')).toEqual('2020-05-11T18:53');
  });
  it('should show a relative date input when date type is date in the future or past is selected', async () => {
    // Given
    render(
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
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
    expect(
      screen.getByText('pimee_catalog_rule.form.date.hour')
    ).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.date.minute')
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
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
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
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
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
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
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
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
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
    render(
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
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

    render(
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
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
    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.BETWEEN,
            value: ['2020-05-10T10:18', '2021-02-18T19:52'],
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[0].value', type: 'custom'},
      {name: 'content.conditions[0].operator', type: 'custom'},
    ];

    render(
      <DateSystemConditionLine
        condition={{
          field: 'created',
          operator: Operator.EQUALS,
          value: '',
        }}
        lineNumber={0}
      />,
      {all: true},
      {toRegister, defaultValues}
    );

    // Then
    const inputFromDateTime = (await screen.findByTestId(
      'date-input-from-0'
    )) as HTMLInputElement;
    expect(inputFromDateTime.getAttribute('value')).toEqual('2020-05-10T10:18');
    const inputToDateTime = screen.getByTestId(
      'date-input-to-0'
    ) as HTMLInputElement;
    expect(inputToDateTime.getAttribute('value')).toEqual('2021-02-18T19:52');
  });
});
