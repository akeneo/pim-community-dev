import React from 'react';
import { useFormContext } from 'react-hook-form';
import { SmallHelper } from '../../../components/HelpersInfos/SmallHelper';

type Props = {
  lineNumber: number;
  type: 'actions' | 'conditions';
};

const getErrorMessages = (obj: { [key: string]: any }): string[] => {
  const nestedMessages = Object.values(obj)
    .filter(
      value =>
        typeof value.message === 'undefined' ||
        typeof value.type === 'undefined'
    )
    .map(value => getErrorMessages(value))
    .reduce((results, errorMessages) => results.concat(errorMessages), []);

  return Object.values(obj)
    .filter(
      value =>
        typeof value.message !== 'undefined' &&
        typeof value.type !== 'undefined'
    )
    .map(value => value.message)
    .concat(nestedMessages);
};

const LineErrors: React.FC<Props> = ({ lineNumber, type }) => {
  const { errors } = useFormContext();
  const currentErrors: {
    [fieldName: string]: { type: string; message: string };
  } = errors?.content?.[type]?.[lineNumber] || {};
  const messages = getErrorMessages(currentErrors);
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
