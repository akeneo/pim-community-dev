import React from 'react';
import { useFormContext } from 'react-hook-form';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FamilySelector } from '../../../../components/Selectors/FamilySelector';
import { SetFamilyAction } from '../../../../models/actions';
import { FamilyCode } from '../../../../models';
import { ActionTitle } from './ActionLine';

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
  const { watch, setValue } = useFormContext();

  useValueInitialization(
    `content.actions[${lineNumber}]`,
    {
      type: 'set',
      field: 'family',
      value: action.value,
    },
    {},
    [action]
  );

  const getValueFormValue: () => FamilyCode | null = () =>
    watch(`content.actions[${lineNumber}].value`);

  const setValueFormValue = (value: FamilyCode | null) =>
    setValue(`content.actions[${lineNumber}].value`, value);

  return (
    <ActionTemplate
      translate={translate}
      title={translate('pimee_catalog_rule.form.edit.actions.set_family.title')}
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <ActionTitle>
        {translate('pimee_catalog_rule.form.edit.actions.set_family.subtitle')}
      </ActionTitle>
      <div className={'AknFormContainer'}>
        <FamilySelector
          router={router}
          id={`edit-rules-action-${lineNumber}-value`}
          label={`${translate(
            'pim_enrich.entity.family.uppercase_label'
          )} ${translate('pim_common.required_label')}`}
          currentCatalogLocale={currentCatalogLocale}
          value={getValueFormValue()}
          onChange={setValueFormValue}
          placeholder={translate(
            'pimee_catalog_rule.form.edit.actions.set_family.subtitle'
          )}
        />
      </div>
    </ActionTemplate>
  );
};

export { SetFamilyActionLine };
