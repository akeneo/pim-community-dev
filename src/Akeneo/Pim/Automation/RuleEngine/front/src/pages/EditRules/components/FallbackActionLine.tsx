import React from 'react';
import { FallbackAction } from '../../../models/FallbackAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from '../ActionLineProps';
import { InputText } from '../../../components/Inputs';

type Props = {
  action: FallbackAction;
} & ActionLineProps;

const FallbackActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  register,
}) => {
  return (
    <ActionTemplate
      translate={translate}
      title='Unknown Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      srOnly='This feature is under development. Please use the import to manage your rules.'>
      <InputText
        name={`content.actions[${lineNumber}]`}
        ref={register}
        disabled
        readOnly
        hiddenLabel={true}
      />
    </ActionTemplate>
  );
};

export { FallbackActionLine };
