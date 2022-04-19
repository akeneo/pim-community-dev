import React from 'react';
import {Field, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AttributeTarget, isTargetNotEmptyAction} from '../../../models';

type ActionIfNotEmptyProps = {
  target: AttributeTarget;
  onTargetChange: (target: AttributeTarget) => void;
};

const ActionIfNotEmpty = ({target, onTargetChange}: ActionIfNotEmptyProps) => {
  const translate = useTranslate();

  const handleActionIfNotEmptyChange = (actionIfNotEmpty: string) => {
    if (isTargetNotEmptyAction(actionIfNotEmpty)) {
      onTargetChange({...target, action_if_not_empty: actionIfNotEmpty});
    }
  };

  return (
    <Field label={translate('akeneo.tailored_import.data_mapping.target.action_if_not_empty.title')}>
      <SelectInput
        emptyResultLabel={translate('pim_common.no_result')}
        onChange={handleActionIfNotEmptyChange}
        value={target.action_if_not_empty}
        clearable={false}
        openLabel={translate('pim_common.open')}
      >
        <SelectInput.Option
          title={translate('akeneo.tailored_import.data_mapping.target.action_if_not_empty.add')}
          value="add"
        >
          {translate('akeneo.tailored_import.data_mapping.target.action_if_not_empty.add')}
        </SelectInput.Option>
        <SelectInput.Option
          title={translate('akeneo.tailored_import.data_mapping.target.action_if_not_empty.set')}
          value="set"
        >
          {translate('akeneo.tailored_import.data_mapping.target.action_if_not_empty.set')}
        </SelectInput.Option>
      </SelectInput>
    </Field>
  );
};

export {ActionIfNotEmpty};
