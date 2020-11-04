import React from 'react';
import styled from 'styled-components';

const DivErrMsg = styled.div`
  font-size: 11px;
  margin-top: 5px;
  line-height: 15px;
  background-size: 16px;
`;

const InputErrorMsg: React.FC<React.HTMLAttributes<HTMLDivElement>> = ({
  children,
  id,
}) => {
  return (
    <DivErrMsg
      className='AknFieldContainer-validationError'
      id={id}
      role='alert'>
      {children}
    </DivErrMsg>
  );
};

export {InputErrorMsg};
