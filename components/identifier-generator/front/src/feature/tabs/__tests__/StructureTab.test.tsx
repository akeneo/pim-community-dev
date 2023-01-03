import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {StructureTab, StructureWithIdentifiers} from '../StructureTab';
import {PROPERTY_NAMES, Structure} from '../../models';
import initialGenerator from '../../tests/fixtures/initialGenerator';

jest.mock('../structure/AddPropertyButton');
jest.mock('../structure/DelimiterEdit');

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
    render(
      <StructureTab
        initialStructure={structure}
        delimiter={'--'}
        onStructureChange={jest.fn()}
        onDelimiterChange={jest.fn()}
        validationErrors={[]}
      />
    );
    expect(screen.getByText('pim_identifier_generator.structure.title')).toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.structure.empty.title')).not.toBeInTheDocument();
    expect(screen.getByText('AddPropertyButtonMock')).toBeInTheDocument();
    expect(screen.getByText('DelimiterEditMock')).toBeInTheDocument();
    expect(screen.getAllByText('AKN')).toHaveLength(2);
  });

  it('should add a new property', () => {
    const onStructureChange = jest.fn();
    render(
      <StructureTab
        initialStructure={[]}
        delimiter={null}
        onStructureChange={onStructureChange}
        onDelimiterChange={jest.fn()}
        validationErrors={[]}
      />
    );

    expect(screen.getByText('pim_identifier_generator.structure.empty.title')).toBeInTheDocument();
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
        onDelimiterChange={jest.fn()}
        validationErrors={[]}
      />
    );

    expect(screen.getAllByText('original value')).toHaveLength(2); // Preview + Line
    fireEvent.click(screen.getAllByText('original value')[1]);
    expect(screen.getByText('pim_identifier_generator.structure.settings.free_text.title')).toBeInTheDocument();
    expect(screen.getByTitle('original value')).toBeInTheDocument();
    fireEvent.change(screen.getByTitle('original value'), {target: {value: 'updated value'}});
    expect(onStructureChange).toBeCalledWith(expect.any(Array));
  });

  it('should delete a property', () => {
    const onStructureChange = jest.fn();
    render(
      <StructureTab
        initialStructure={initialGenerator.structure}
        delimiter={null}
        onStructureChange={onStructureChange}
        onDelimiterChange={jest.fn()}
        validationErrors={[]}
      />
    );

    fireEvent.click(screen.getByText('pim_common.delete'));
    expect(screen.getByText('pim_identifier_generator.list.confirmation')).toBeInTheDocument();
    fireEvent.click(screen.getAllByText('pim_common.delete')[1]);
    expect(onStructureChange).toBeCalledWith([]);
  });

  it('should cancel deletion of a property', () => {
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
        onDelimiterChange={jest.fn()}
        validationErrors={[]}
      />
    );

    fireEvent.click(screen.getByText('pim_common.delete'));
    expect(screen.getByText('pim_identifier_generator.list.confirmation')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.cancel'));
    expect(screen.queryByText('pim_identifier_generator.list.confirmation')).not.toBeInTheDocument();
  });

  it('should toggle off the delimiter', () => {
    const onStructureChange = jest.fn();
    const onDelimiterChange = jest.fn();
    render(
      <StructureTab
        initialStructure={[
          {
            type: PROPERTY_NAMES.FREE_TEXT,
            string: 'original value',
          },
        ]}
        delimiter={'--'}
        onStructureChange={onStructureChange}
        onDelimiterChange={onDelimiterChange}
        validationErrors={[]}
      />
    );

    expect(screen.getByTestId('current_delimiter').textContent).toEqual('--');
    expect(screen.getByText('Toggle Delimiter')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Toggle Delimiter'));
    expect(onDelimiterChange).toBeCalledWith(null);
  });

  it('should toggle on the delimiter', () => {
    const onStructureChange = jest.fn();
    const onDelimiterChange = jest.fn();
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
        onDelimiterChange={onDelimiterChange}
        validationErrors={[]}
      />
    );

    expect(screen.getByTestId('current_delimiter').textContent).toEqual('');
    expect(screen.getByText('Toggle Delimiter')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Toggle Delimiter'));
    expect(onDelimiterChange).toBeCalledWith('-');
  });

  it('should not display add property button when limit is reached', () => {
    render(
      <StructureTab
        initialStructure={[
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
        ]}
        delimiter={null}
        onStructureChange={jest.fn()}
        onDelimiterChange={jest.fn()}
        validationErrors={[]}
      />
    );
    expect(screen.queryByText('AddPropertyButtonMock')).not.toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.structure.limit_reached')).toBeInTheDocument();
  });

  it('should reorder properties', () => {
    render(
      <StructureTab
        initialStructure={[
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'abc'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'def'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'ijk'},
          {type: PROPERTY_NAMES.FREE_TEXT, string: 'lmn'},
        ]}
        delimiter={null}
        onStructureChange={jest.fn()}
        onDelimiterChange={jest.fn()}
        validationErrors={[]}
      />
    );

    let dataTransferred = '';
    const dataTransfer = {
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      getData: (_format: string) => {
        return dataTransferred;
      },
      setData: (_format: string, data: string) => {
        dataTransferred = data;
      },
    };

    // Move 2nd item after 4th one
    fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[1]);
    fireEvent.dragStart(screen.getAllByRole('row')[1], {dataTransfer});
    fireEvent.dragEnter(screen.getAllByRole('row')[2], {dataTransfer});
    fireEvent.dragLeave(screen.getAllByRole('row')[2], {dataTransfer});
    fireEvent.dragEnter(screen.getAllByRole('row')[3], {dataTransfer});
    fireEvent.drop(screen.getAllByRole('row')[3], {dataTransfer});
    fireEvent.dragEnd(screen.getAllByRole('row')[1], {dataTransfer});

    const rows = screen.getAllByRole('row');
    expect(rows.map(row => row.textContent?.substr(0, 3))).toEqual(['abc', 'ijk', 'lmn', 'def']);
  });

  it('should show displayed errors', () => {
    const structure: StructureWithIdentifiers = [
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'First item', id: 'id0'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: '', id: 'id1'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'Third item', id: 'id2'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: '', id: 'id3'},
    ];
    render(
      <StructureTab
        initialStructure={structure}
        delimiter={null}
        onStructureChange={jest.fn()}
        onDelimiterChange={jest.fn()}
        validationErrors={[
          {path: 'structure[1].string', message: 'error on second item'},
          {path: 'structure[2].string', message: 'similar error'},
          {path: 'structure[3].string', message: 'similar error'},
        ]}
      />
    );

    expect(screen.getAllByRole('alert').length).toBe(3);
    expect(screen.getAllByText('error on second item').length).toBe(1);
    expect(screen.getAllByText('similar error').length).toBe(1);
  });
});
