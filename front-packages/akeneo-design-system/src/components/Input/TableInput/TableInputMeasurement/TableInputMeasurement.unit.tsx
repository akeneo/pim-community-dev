import React from 'react';
import {render, screen, fireEvent} from '../../../../storybook/test-util';
import {TableInputMeasurement} from './TableInputMeasurement';

test('it renders its children properly', () => {
  render(
    <table>
      <thead>
        <tr>
          <td>
            <TableInputMeasurement
              onChange={jest.fn()}
              openLabel={'open'}
              emptyResultLabel={'no results'}
              amount={'42'}
              unit={'METER'}
              units={[{value: 'METER', symbol: 'm', label: 'Meter'}]}
            />
          </td>
        </tr>
      </thead>
    </table>
  );

  expect(screen.getByTitle('42')).toBeInTheDocument();
  expect(screen.getByText('m')).toBeInTheDocument();
});

test('it callback change amount', () => {
  const handleChange = jest.fn();
  render(
    <table>
      <thead>
        <tr>
          <td>
            <TableInputMeasurement
              onChange={handleChange}
              openLabel={'open'}
              emptyResultLabel={'no results'}
              amount={'42'}
              unit={'METER'}
              units={[{value: 'METER', symbol: 'm', label: 'Meter'}]}
            />
          </td>
        </tr>
      </thead>
    </table>
  );

  fireEvent.change(screen.getByTitle('42'), {target: {value: '69'}});
  expect(handleChange).toBeCalledWith('69', 'METER');
});

test('it callback change unit', () => {
  const handleChange = jest.fn();
  render(
    <table>
      <thead>
        <tr>
          <td>
            <TableInputMeasurement
              onChange={handleChange}
              openLabel={'open'}
              emptyResultLabel={'no results'}
              amount={'42'}
              unit={'METER'}
              units={[
                {value: 'METER', symbol: 'm', label: 'Meter'},
                {value: 'MILLIMETER', symbol: 'mm', label: 'Millimeter'},
              ]}
            />
          </td>
        </tr>
      </thead>
    </table>
  );

  fireEvent.click(screen.getByTitle('open'));
  expect(screen.getByText('mm')).toBeInTheDocument();
  fireEvent.click(screen.getByText('mm'));
  expect(handleChange).toBeCalledWith('42', 'MILLIMETER');
});

test('TableInputMeasurement supports ...rest props', () => {
  render(
    <table>
      <thead>
        <tr>
          <td>
            <TableInputMeasurement
              onChange={jest.fn()}
              openLabel={'open'}
              emptyResultLabel={'no results'}
              amount={''}
              unit={'METER'}
              units={[]}
              data-testid={'my_value'}
            />
          </td>
        </tr>
      </thead>
    </table>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
