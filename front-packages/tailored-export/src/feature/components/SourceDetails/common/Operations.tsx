import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const OperationHeader = styled.div`
  font-size: ${getFontSize('bigger')};
  color: ${getColor('grey', 140)};
  margin: 20px 0;
`;

type OperationsProps = {
  children: ReactNode;
};

const Operations = ({children}: OperationsProps) => {
  const translate = useTranslate();

  return (
    <div>
      <OperationHeader>{translate('akeneo.tailored_export.column_details.sources.operation.header')}</OperationHeader>
      <div>{children}</div>
    </div>
  );
};

export {Operations};
