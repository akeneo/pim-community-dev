import React from 'react';
import {useTranslate, Section, ValidationError} from '@akeneo-pim-community/shared';
import {Field, Helper, MultiSelectInput} from 'akeneo-design-system';

type QualityScores = string[];
type QualityScoreSelectorProps = {
  availableQualityScores: QualityScores;
  qualityScore: QualityScores;
  onChange: (newQualityScores: QualityScores) => void;
  validationErrors: ValidationError[];
};

const AVAILABLE_QUALITY_SCORES = ['A', 'B', 'C', 'D', 'E'];

const QualityScoreSelector = ({
  availableQualityScores,
  qualityScore,
  onChange,
  validationErrors,
}: QualityScoreSelectorProps) => {
  const translate = useTranslate();

  return (
    <Section>
      <Field label={translate('pim_enrich.export.product.filter.quality-score.title')}>
        <MultiSelectInput
          value={qualityScore}
          onChange={onChange}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          placeholder={translate('pim_enrich.export.product.filter.quality-score.empty_selection')}
          removeLabel={translate('akeneo.tailored_export.filters.quality_score.locales.remove')}
        >
          {availableQualityScores.map((qualityScore: string) => (
            <MultiSelectInput.Option key={qualityScore} title={qualityScore} value={qualityScore}>
              {qualityScore}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
        {validationErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Section>
  );
};

export {QualityScoreSelector, AVAILABLE_QUALITY_SCORES};
export type {QualityScores};
