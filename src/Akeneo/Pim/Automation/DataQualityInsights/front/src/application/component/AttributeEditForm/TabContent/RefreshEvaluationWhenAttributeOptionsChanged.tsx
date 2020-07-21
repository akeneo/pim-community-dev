import React, {FC, useEffect, useState} from 'react';
import {useAttributeOptionsListContext} from "../../../context/AttributeOptionsListContext";
import {useAttributeSpellcheckEvaluationContext} from "../../../context/AttributeSpellcheckEvaluationContext";
import {ATTRIBUTE_EDIT_FORM_UPDATED} from "../../../constant";
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model/AttributeOption.interface';

type AttributeOptionsList = AttributeOption[]|null

const RefreshEvaluationWhenAttributeOptionsChanged: FC = () => {
  const [previousList, setPreviousList] = useState<AttributeOptionsList>(null);
  const {attributeOptions} = useAttributeOptionsListContext();
  const {refresh} = useAttributeSpellcheckEvaluationContext()

  useEffect(() => {
    if (attributeOptions !== previousList) {
      setPreviousList(attributeOptions);
      (async () => refresh())();
      window.dispatchEvent(new CustomEvent(ATTRIBUTE_EDIT_FORM_UPDATED));
    }
  }, [attributeOptions])

  return <></>;
};

export default RefreshEvaluationWhenAttributeOptionsChanged;
