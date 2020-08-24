import React from 'react';
import { useFormContext } from 'react-hook-form';
import { useControlledFormInputAction } from '../../../hooks';
import { AttributePreview } from '../attribute/AttributePreview';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { ConcatenateSource } from "../../../../../models/actions";

type Props = {
  lineNumber: number;
};

const ConcatenatePreview: React.FC<Props> = ({ lineNumber }) => {
  const translate = useTranslate();
  const { watch } = useFormContext();
  const { formName } = useControlledFormInputAction<string | null>(lineNumber);
  const sources = () => watch(formName('from'));

  return (
    <div className={'AknRulePreviewBox'}>
      <div className={'AknRulePreviewBox-title'}>
        {translate('pimee_catalog_rule.form.edit.preview')}
      </div>
      <div data-testid={'calculate-preview'}>
        {Array.isArray(sources()) &&
        sources().length > 0 &&
        sources().map((source: ConcatenateSource, i: number) => {
          return (
            <span key={i}>
              <AttributePreview attributeCode={source.field}/>
              &nbsp;
            </span>
          )
        })
        }
      </div>
    </div>
  );
};

export { ConcatenatePreview };
