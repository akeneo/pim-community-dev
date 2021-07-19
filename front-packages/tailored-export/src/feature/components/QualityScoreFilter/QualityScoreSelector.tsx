import React from 'react';
import {useTranslate, Section, ValidationError} from '@akeneo-pim-community/shared';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {Operator} from '../CompletenessFilter/OperatorSelector';

type QualityScore = string;
type QualityScoreSelectorProps = {
  availableQualityScores: string[];
  qualityScore: QualityScore;
  onChange: (newQualityScore: QualityScore) => void;
  validationErrors: ValidationError[];
};

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
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={qualityScore}
          onChange={onChange}
        >
          {availableQualityScores.map((qualityScore: Operator) => {
            const qualityScoreLabel =
              qualityScore !== 'NO_CONDITION_ON_QUALITY_SCORE'
                ? qualityScore
                : translate(`pim_enrich.export.product.filter.quality-score.empty_selection`);

            return (
              <SelectInput.Option key={qualityScore} title={qualityScoreLabel} value={qualityScore}>
                {translate(qualityScoreLabel)}
              </SelectInput.Option>
            );
          })}
        </SelectInput>
        {validationErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Section>
  );
};

export {QualityScoreSelector};
export type {QualityScore};
