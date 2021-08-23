import React from 'react';
import {useTranslate, Section, ValidationError} from '@akeneo-pim-community/shared';
import {Field, Helper, MultiSelectInput} from 'akeneo-design-system';

const AVAILABLE_QUALITY_SCORES = ['A', 'B', 'C', 'D', 'E'];
type QualityScore = typeof AVAILABLE_QUALITY_SCORES[number];
type QualityScores = QualityScore[];

type QualityScoreSelectorProps = {
  availableQualityScores: QualityScores;
  qualityScores: QualityScores;
  onChange: (newQualityScores: QualityScores) => void;
  validationErrors: ValidationError[];
};

const QualityScoreSelector = ({
  availableQualityScores,
  qualityScores,
  onChange,
  validationErrors,
}: QualityScoreSelectorProps) => {
  const translate = useTranslate();

  return (
    <Section>
      <Field label={translate('pim_enrich.export.product.filter.quality-score.title')}>
        <MultiSelectInput
          value={qualityScores}
          onChange={onChange}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          placeholder={translate('pim_enrich.export.product.filter.quality-score.empty_selection')}
          removeLabel={translate('akeneo.tailored_export.filters.quality_score.quality_score.remove')}
        >
          {availableQualityScores.map((qualityScore: QualityScore) => (
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
export type {QualityScores, QualityScore};
