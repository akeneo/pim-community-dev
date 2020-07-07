import React from 'react';
import { useFormContext } from 'react-hook-form';
import { SmallHelper } from '../../../components/HelpersInfos/SmallHelper';

type Props = {
  lineNumber: number;
  type: 'actions' | 'conditions';
};

const LineErrors: React.FC<Props> = ({ lineNumber, type }) => {
  const { errors } = useFormContext();
  const currentErrors: {
    [fieldName: string]: { type: string; message: string };
  } = errors?.content?.[type]?.[lineNumber] || {};
  const messages = Object.values(currentErrors).map(
    fieldError => fieldError.message
  );
  return (
    <>
      {messages.map((message, i) => (
        <SmallHelper level='error' key={`${lineNumber}-${i}`}>
          {message}
        </SmallHelper>
      ))}
    </>
  );
};

export { LineErrors };
