import React from 'react';
import { useFormContext } from 'react-hook-form';
import { ErrorLine } from './style';

type Props = {
  lineNumber: number;
  type: 'actions'|'conditions';
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
    <ErrorLine>
      {messages.map((message, i) => {
        return <li key={i}>{message}</li>;
      })}
    </ErrorLine>
  );
};

export { LineErrors };
