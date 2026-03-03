import React from 'react';
import {Delimiter} from '../../../models';

type DelimiterProps = {
  delimiter: Delimiter | null;
  onToggleDelimiter: () => void;
  onChangeDelimiter: (text: string) => void;
};

const DelimiterEdit: React.FC<DelimiterProps> = ({delimiter, onToggleDelimiter, onChangeDelimiter}) => {
  const updateDelimiter = () => {
    onChangeDelimiter('/');
  };

  return (
    <>
      DelimiterEditMock
      <span data-testid={'current_delimiter'}>{delimiter}</span>
      <button onClick={onToggleDelimiter}>Toggle Delimiter</button>
      <button onClick={updateDelimiter}>Update Delimiter</button>
    </>
  );
};

export {DelimiterEdit};
