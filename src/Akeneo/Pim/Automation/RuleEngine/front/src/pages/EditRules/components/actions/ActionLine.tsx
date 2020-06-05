import React from 'react';
import { Action } from '../../../../models/Action';
import { ActionLineProps } from './ActionLineProps';
import styled from 'styled-components';
import { getActionModule } from "../../../../models/rule-definition-denormalizer";

const ActionTitle = styled.div`
  color: ${({ theme }): string => theme.color.purple100};
  font-size: 20px;
  line-height: 40px;
  margin-bottom: 15px;
`;

const ActionGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(2, minmax(140px, 1fr));
`;
const ActionLeftSide = styled.div`
  grid-column: 1;
  vertical-align: top;
  padding-right: 20px;
`;

const ActionRightSide = styled(ActionLeftSide)`
  border-left: 1px solid ${({ theme }): string => theme.color.purple100};
  grid-column: 2;
  vertical-align: top;
  padding-left: 20px;
`;

const ActionLine: React.FC<{ action: Action } & ActionLineProps> = ({
  action,
  lineNumber,
  handleDelete,
  currentCatalogLocale,
  locales,
  scopes,
}) => {
  const Line = getActionModule(action) as React.FC<ActionLineProps & { action: Action }>;

  return (
    <Line
      action={action}
      lineNumber={lineNumber}
      handleDelete={handleDelete}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
    />
  );
};

export { ActionLine, ActionTitle, ActionGrid, ActionLeftSide, ActionRightSide };
