import React, {useState} from 'react';
import {useTranslate} from "@akeneo-pim-community/shared";
import {Field, Helper, TextInput} from "akeneo-design-system";

type useCodeInputProps = {
  defaultCode?: string
  generatedFromLabel?: string;
}

const useAttributeCodeInput: (props: useCodeInputProps) => any = ({
  defaultCode,
  generatedFromLabel,
}) => {
  const translate = useTranslate();
  const [code, setCode] = useState<string>(defaultCode || '');
  const [isCodeDirty, setCodeDirty] = useState<boolean>((defaultCode || '') !== '');

  React.useEffect(() => {
    if (!isCodeDirty) {
      const code = (generatedFromLabel || '').replace(/[^a-zA-Z0-9_]/gi, '_').substring(0, 255);
      setCode(code);
    }
  }, [generatedFromLabel]);

  const handleCodeChange = (code: string) => {
    setCode(code);
    setCodeDirty(true);
  }

  const codeViolations: string[] = [];
  if (code === '') {
    codeViolations.push(translate('pim_enrich.entity.attribute.property.code.must_be_filled'));
  }
  if (code !== '' && !/^[a-zA-Z0-9_]+$/.exec(code)) {
    codeViolations.push(translate('pim_enrich.entity.attribute.property.code.invalid'));
  }
  if (code !== '' && (
    /^(id|family)$/i.exec(code) ||
    /^(associationTypes|categories|categoryId|completeness|enabled|groups|associations|products|scope|treeId|values|category|parent|label|.*_products|.*_groups|entity_type|attributes)$/.exec(code))
  ) {
    codeViolations.push(translate('pim_enrich.entity.attribute.property.code.not_available'));
  }

  const CodeField = <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
    <TextInput
      characterLeftLabel={translate(
        'pim_common.characters_left',
        {count: 255 - code.length},
        255 - code.length
      )}
      value={code}
      onChange={handleCodeChange}
      maxLength={255}
    />
    {(isCodeDirty || generatedFromLabel !== '') && codeViolations.map((violation, i) => <Helper key={i} level="error">{violation}</Helper>)}
  </Field>

  return [code, CodeField, codeViolations.length === 0];
}

export { useAttributeCodeInput }
