import React from 'react';
import { useFormContext } from 'react-hook-form';
import styled from 'styled-components';
import { SmallHelper } from '../../../components/HelpersInfos/SmallHelper';

type Props = {
  lineNumber: number;
  type: 'actions' | 'conditions';
};

const SmallHelperCondition = styled(SmallHelper)`
  margin-left: 10%;
  margin-top: 15px;
`;

const LineErrors: React.FC<Props> = ({ lineNumber, type }) => {
  const { errors } = useFormContext();
  const currentErrors: {
    [fieldName: string]: { type: string; message: string };
  } = errors?.content?.[type]?.[lineNumber] || {};
  const messages = Object.values(currentErrors).map(
    fieldError => fieldError.message
  );

  if (type === 'actions') {
    return (
      <SmallHelper level='error'>
        {messages.map((message, i) => {
          return <li key={i}>{message}</li>;
        })}
      </SmallHelper>
    );
  }

  return (
    <SmallHelperCondition level='error'>
      {messages.map((message, i) => {
        return <li key={i}>{message}</li>;
      })}
    </SmallHelperCondition>
  );
};

export { LineErrors };
