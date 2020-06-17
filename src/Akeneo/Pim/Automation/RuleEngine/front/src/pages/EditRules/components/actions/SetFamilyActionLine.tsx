import React from 'react';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FamilySelector } from '../../../../components/Selectors/FamilySelector';
import { SetFamilyAction } from '../../../../models/actions';
import { ActionTitle } from './ActionLine';
import { useRegisterConsts } from '../../hooks/useRegisterConst';
import { useTranslate } from '../../../../dependenciesTools/hooks';

type Props = {
  action: SetFamilyAction;
} & ActionLineProps;

const SetFamilyActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
  currentCatalogLocale,
}) => {
  const translate = useTranslate();

  useRegisterConsts(
    {
      type: 'set',
      field: 'family',
    },
    `content.actions[${lineNumber}]`
  );

  return (
    <ActionTemplate
      title={translate('pimee_catalog_rule.form.edit.actions.set_family.title')}
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <ActionTitle>
        {translate('pimee_catalog_rule.form.edit.actions.set_family.subtitle')}
      </ActionTitle>
      <div className={'AknFormContainer'}>
        <FamilySelector
          label={`${translate(
            'pim_enrich.entity.family.uppercase_label'
          )} ${translate('pim_common.required_label')}`}
          currentCatalogLocale={currentCatalogLocale}
          value={action.value}
          placeholder={translate(
            'pimee_catalog_rule.form.edit.actions.set_family.subtitle'
          )}
          name={`content.actions[${lineNumber}].value`}
        />
      </div>
    </ActionTemplate>
  );
};

export { SetFamilyActionLine };
