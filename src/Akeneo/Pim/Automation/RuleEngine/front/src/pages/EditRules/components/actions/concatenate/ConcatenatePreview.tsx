import React from 'react';
import { useFormContext } from 'react-hook-form';
import { useControlledFormInputAction } from '../../../hooks';
import { AttributePreview } from '../attribute/AttributePreview';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { ConcatenateSource } from '../../../../../models/actions';

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
      <div data-testid={'concatenate-preview'}>
        {Array.isArray(sources()) &&
          sources().length > 0 &&
          sources().map((source: ConcatenateSource, i: number) => {
            const lastSource = i > 0 ? sources()[i - 1] : null;

            return (
              <span key={i}>
                {source.field && lastSource?.field && <>&nbsp;</>}
                {source.field && (
                  <AttributePreview attributeCode={source.field} />
                )}
                {'undefined' !== typeof source.new_line && <br />}
                {source.text}
              </span>
            );
          })}
      </div>
    </div>
  );
};

export { ConcatenatePreview };
