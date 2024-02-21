import React from 'react';
import {fireEvent, mockACLs, render, screen} from '../../tests/test-utils';
import {DelimiterEdit} from '../structure';

describe('SelectionTab', () => {
  it('should render the delimiter edit from', () => {
    render(<DelimiterEdit delimiter={null} onToggleDelimiter={jest.fn()} onChangeDelimiter={jest.fn()} />);

    expect(screen.getByText('pim_identifier_generator.structure.delimiters.title')).toBeInTheDocument();
  });

  it('should enable delimiters', () => {
    const onToggleDelimiter = jest.fn();
    render(<DelimiterEdit delimiter={null} onToggleDelimiter={onToggleDelimiter} onChangeDelimiter={jest.fn()} />);

    expect(screen.getByText('pim_identifier_generator.structure.delimiters.checkbox_label')).toBeInTheDocument;
    expect(screen.queryByText('pim_identifier_generator.structure.delimiters.input_label')).not.toBeInTheDocument;
    fireEvent.click(screen.getByText('pim_identifier_generator.structure.delimiters.checkbox_label'));
    expect(onToggleDelimiter).toBeCalled();
  });

  it('should edit and disable delimiters', () => {
    const onToggleDelimiter = jest.fn();
    const onChangeDelimiter = jest.fn();
    render(
      <DelimiterEdit delimiter={'-'} onToggleDelimiter={onToggleDelimiter} onChangeDelimiter={onChangeDelimiter} />
    );

    expect(screen.getByText('pim_identifier_generator.structure.delimiters.checkbox_label')).toBeInTheDocument;
    expect(screen.getByText('pim_identifier_generator.structure.delimiters.input_label')).toBeInTheDocument;

    const delimiterInput = screen.getByRole('textbox', {
      name: 'pim_identifier_generator.structure.delimiters.input_label',
    });
    fireEvent.change(delimiterInput, {target: {value: '//'}});
    expect(onChangeDelimiter).toBeCalledWith('//');

    fireEvent.click(screen.getByText('pim_identifier_generator.structure.delimiters.checkbox_label'));
    expect(onToggleDelimiter).toBeCalled();
  });

  it('should make the delimiter toggle readonly if the user doesnt have the correct acl', () => {
    mockACLs(true, false);
    render(<DelimiterEdit delimiter={null} onToggleDelimiter={jest.fn()} onChangeDelimiter={jest.fn()} />);

    const checkbox = screen.getByRole('checkbox');
    expect(checkbox).toBeInTheDocument;
    expect(checkbox).toHaveAttribute('readonly');
  });
});
