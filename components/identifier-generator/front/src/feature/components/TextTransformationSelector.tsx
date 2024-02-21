import React, {FC} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {TEXT_TRANSFORMATION, TextTransformation} from '../models';
import {useIdentifierGeneratorAclContext} from '../context';

type TextTransformationSelectorProps = {
  value: TextTransformation;
  onChange: (textTransformation: TextTransformation) => void;
};

const TextTransformationSelector: FC<TextTransformationSelectorProps> = ({value, onChange}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  const handleChange = (textTransformation: string) => {
    onChange(textTransformation as TextTransformation);
  };

  return (
    <SelectInput
      openLabel={translate('pim_common.open')}
      value={value}
      emptyResultLabel={''}
      onChange={handleChange}
      clearable={false}
      readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
    >
      {[TEXT_TRANSFORMATION.NO, TEXT_TRANSFORMATION.UPPERCASE, TEXT_TRANSFORMATION.LOWERCASE].map(
        textTransformation => (
          <SelectInput.Option
            key={textTransformation}
            title={translate(`pim_identifier_generator.general.text_transformation.${textTransformation}`)}
            value={textTransformation}
          >
            {translate(`pim_identifier_generator.general.text_transformation.${textTransformation}`)}
          </SelectInput.Option>
        )
      )}
    </SelectInput>
  );
};

export {TextTransformationSelector};
