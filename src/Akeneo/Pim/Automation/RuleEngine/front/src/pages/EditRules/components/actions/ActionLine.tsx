import React from 'react';
import { Action } from '../../../../models/Action';
import { ActionLineProps } from '../../ActionLineProps';

const ActionLine: React.FC<{ action: Action } & ActionLineProps> = ({
  action,
  translate,
  lineNumber,
  handleDelete,
}) => {
  const Line = action.module;

  return (
    <Line
      action={action}
      translate={translate}
      lineNumber={lineNumber}
      handleDelete={handleDelete}
    />
  );
};

export { ActionLine };
