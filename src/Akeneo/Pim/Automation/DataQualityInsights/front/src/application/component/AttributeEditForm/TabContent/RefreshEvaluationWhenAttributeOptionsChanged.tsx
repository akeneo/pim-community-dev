import React, {FC, useEffect, useState} from 'react';
import {useAttributeOptionsListContext} from '../../../context/AttributeOptionsListContext';
import {useAttributeSpellcheckEvaluationContext} from '../../../context/AttributeSpellcheckEvaluationContext';
import {ATTRIBUTE_EDIT_FORM_UPDATED} from '../../../constant';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model/AttributeOption.interface';
import {useMountedState} from '../../../../infrastructure/hooks/Common/useMountedState';

type AttributeOptionsList = AttributeOption[] | null;

const RefreshEvaluationWhenAttributeOptionsChanged: FC = () => {
  const [updatedOptions, setUpdatedOptions] = useState<AttributeOptionsList>(null);
  const {attributeOptions} = useAttributeOptionsListContext();
  const {refresh} = useAttributeSpellcheckEvaluationContext();
  const {isMounted} = useMountedState();

  useEffect(() => {
    if (attributeOptions !== updatedOptions) {
      setUpdatedOptions(attributeOptions);
    }
  }, [attributeOptions]);

  useEffect(() => {
    if (updatedOptions !== null) {
      refresh().then(() => {
        if (isMounted()) {
          window.dispatchEvent(new CustomEvent(ATTRIBUTE_EDIT_FORM_UPDATED));
        }
      });
    }
  }, [updatedOptions]);

  return <></>;
};

export default RefreshEvaluationWhenAttributeOptionsChanged;
