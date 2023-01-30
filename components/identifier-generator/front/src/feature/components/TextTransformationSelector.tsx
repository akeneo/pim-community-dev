import React, {FC} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {TextTransformation} from '../models';

type TextTransformationSelectorProps = {
  value: TextTransformation;
  onChange: (textTransformation: TextTransformation) => void;
};

const TextTransformationSelector: FC<TextTransformationSelectorProps> = ({value, onChange}) => {
  const translate = useTranslate();

  const handleChange = (textTransformation: string) => {
    onChange(textTransformation as TextTransformation);
  };

  return (
    <SelectInput openLabel={translate('pim_common.open')} value={value} emptyResultLabel={''} onChange={handleChange}>
      {['no', 'uppercase', 'lowercase'].map(textTransformation => (
        <SelectInput.Option
          key={textTransformation}
          title={translate(`pim_identifier_generator.general.text_transformation.${textTransformation}`)}
          value={textTransformation}
        >
          {translate(`pim_identifier_generator.general.text_transformation.${textTransformation}`)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {TextTransformationSelector};
