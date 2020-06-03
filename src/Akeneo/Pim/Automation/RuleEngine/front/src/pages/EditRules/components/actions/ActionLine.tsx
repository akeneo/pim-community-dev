import React from 'react';
import { Action } from '../../../../models/Action';
import { ActionLineProps } from './ActionLineProps';
import styled from 'styled-components';

const ActionTitle = styled.div`
  color: ${({ theme }): string => theme.color.purple100};
  font-size: 20px;
  line-height: 40px;
`;

const ActionLine: React.FC<{ action: Action } & ActionLineProps> = ({
  action,
  translate,
  lineNumber,
  handleDelete,
  router,
  currentCatalogLocale,
  locales,
  scopes,
}) => {
  const Line = action.module as React.FC<ActionLineProps & { action: Action }>;

  return (
    <Line
      action={action}
      translate={translate}
      lineNumber={lineNumber}
      handleDelete={handleDelete}
      router={router}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
    />
  );
};

export { ActionLine, ActionTitle };
