import React from 'react';
import {Checkbox} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AttributeTarget} from '../../../models';

type ClearIfEmptyProps = {
  target: AttributeTarget;

  onTargetChange: (target: AttributeTarget) => void;
};

const ClearIfEmpty = ({target, onTargetChange}: ClearIfEmptyProps) => {
  const translate = useTranslate();

  const handleClearIfEmptyChange = (clearIfEmpty: boolean) =>
    onTargetChange({...target, action_if_empty: clearIfEmpty ? 'clear' : 'skip'});

  return (
    <Checkbox checked={'clear' === target.action_if_empty} onChange={handleClearIfEmptyChange}>
      {translate('akeneo.tailored_import.data_mapping.target.clear_if_empty')}
    </Checkbox>
  );
};

export {ClearIfEmpty};
