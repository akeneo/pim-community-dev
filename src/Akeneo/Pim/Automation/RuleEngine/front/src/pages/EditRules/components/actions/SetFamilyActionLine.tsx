import React from 'react';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FamilySelector } from "../../../../components/Selectors/FamilySelector";
import { SetFamilyAction } from "../../../../models/actions";

type Props = {
  action: SetFamilyAction;
} & ActionLineProps;

const SetFamilyActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
  router,
  currentCatalogLocale,
}) => {
  const values: any = {
    type: 'set',
    field: 'family',
    value: action.value,
  };
  useValueInitialization(`content.actions[${lineNumber}]`, values);

  return (
    <ActionTemplate
      translate={translate}
      title='Set Family'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      Select your family
      <FamilySelector
        router={router}
        id={`edit-rules-action-${lineNumber}-value`}
        label={'Family'}
        currentCatalogLocale={currentCatalogLocale}
        value={action.value}
        onChange={() => {
          console.log('OK');
        }}
      />
    </ActionTemplate>
  );
};

export { SetFamilyActionLine };
