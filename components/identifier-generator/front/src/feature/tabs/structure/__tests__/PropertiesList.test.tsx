import React from 'react';
import {render, screen, fireEvent} from '../../../tests/test-utils';
import {PROPERTY_NAMES} from '../../../models';
import {PropertiesList} from '../PropertiesList';
import {StructureWithIdentifiers} from '../../StructureTab';

describe('PropertiesList', () => {
  it('reorder properties', () => {
    const onChange = jest.fn();
    const structure: StructureWithIdentifiers = [
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'First item', id: 'id0'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'Second item', id: 'id1'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'Third item', id: 'id2'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'Fourth item', id: 'id3'},
    ];
    render(<PropertiesList
      structure={structure}
      onChange={onChange}
      selectedId={undefined}
      onSelect={jest.fn()}
    />);


    let dataTransferred = '';
    const dataTransfer = {
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

    expect(onChange).toHaveBeenCalledWith([
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'First item', id: 'id0'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'Third item', id: 'id2'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'Fourth item', id: 'id3'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'Second item', id: 'id1'},
    ]);
  });
});
