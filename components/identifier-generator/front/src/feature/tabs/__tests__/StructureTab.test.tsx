import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {StructureTab} from '../StructureTab';
import {PROPERTY_NAMES, Structure} from '../../models';

jest.mock('../structure/AddPropertyButton');

describe('StructureTab', () => {
  it('should render the structure tab', () => {
    const structure: Structure = [
      {
        type: PROPERTY_NAMES.FREE_TEXT,
        string: 'AKN',
      },
      {
        type: PROPERTY_NAMES.AUTO_NUMBER,
        digitsMin: 10,
        numberMin: 42,
      },
    ];
    render(<StructureTab initialStructure={structure} delimiter={null} onStructureChange={jest.fn()} />);
    expect(screen.getByText('pim_identifier_generator.structure.title')).toBeInTheDocument();
    expect(screen.getByText('AddPropertyButtonMock')).toBeInTheDocument();
    expect(screen.getAllByText('AKN')).toHaveLength(2);
  });

  it('should add a new property', () => {
    const onStructureChange = jest.fn();
    render(<StructureTab initialStructure={[]} delimiter={null} onStructureChange={onStructureChange} />);

    expect(screen.getByText('AddPropertyButtonMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Add Property'));
    expect(onStructureChange).toBeCalledWith(expect.any(Array));
  });

  it('should update a property', () => {
    const onStructureChange = jest.fn();
    render(
      <StructureTab
        initialStructure={[
          {
            type: PROPERTY_NAMES.FREE_TEXT,
            string: 'original value',
          },
        ]}
        delimiter={null}
        onStructureChange={onStructureChange}
      />
    );

    expect(screen.getAllByText('original value')).toHaveLength(2); // Preview + Line
    fireEvent.click(screen.getAllByText('original value')[1]);
    expect(screen.getByText('pim_identifier_generator.structure.settings.free_text.title')).toBeInTheDocument();
    expect(screen.getByTitle('original value')).toBeInTheDocument;
    fireEvent.change(screen.getByTitle('original value'), {target: {value: 'updated value'}});
    expect(onStructureChange).toBeCalledWith(expect.any(Array));
  });
});
