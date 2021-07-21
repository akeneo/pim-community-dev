import React from 'react';
import styled from 'styled-components';
import {
  ChannelCode,
  filterErrors,
  formatParameters,
  getLocalesFromChannel,
  Locale,
  LocaleCode,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {Operator, OperatorSelector} from './OperatorSelector';
import {AVAILABLE_QUALITY_SCORES, QualityScore, QualityScores, QualityScoreSelector} from './QualityScoreSelector';
import {ChannelDropdown} from '../ChannelDropdown';
import {useChannels} from '../../hooks';
import {LocalesSelector} from '../LocalesSelector';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 15px;
  justify-content: space-between;
  width: 100%;
`;

type Filter = {
  field: 'quality_score_multi_locales';
  operator: Operator | null;
  value: number[];
  context?: {
    locales: LocaleCode[];
    scope: ChannelCode;
  };
};
type QualityScoreFilterProps = {
  availableOperators: Operator[];
  filter: Filter;
  onChange: (newFilter: Filter) => void;
  validationErrors: ValidationError[];
};

const QualityScoreFilter = ({availableOperators, filter, onChange, validationErrors}: QualityScoreFilterProps) => {
  const translate = useTranslate();
  const availableChannels = useChannels();
  const availableLocales = getLocalesFromChannel(availableChannels, filter.context?.scope ?? null);
  const formattedValidationErrors = formatParameters(validationErrors);
  const valueErrors = filterErrors(formattedValidationErrors, '[value]');
  const operatorErrors = filterErrors(formattedValidationErrors, '[operator]');
  const scopeErrors = filterErrors(formattedValidationErrors, '[context][scope]');
  const localesErrors = filterErrors(formattedValidationErrors, '[context][locales]');

  const handleOperatorChange = (newOperator: Operator) => {
    onChange({...filter, operator: newOperator});
  };
  const handleLocalesChange = (newLocales: LocaleCode[]) => {
    const newFilter = {...filter, context: {scope: filter.context?.scope ?? '', locales: newLocales}};
    onChange(newFilter);
  };
  const handleChannelChange = (newScope: ChannelCode) => {
    const newAvailableLocaleCodes = getLocalesFromChannel(availableChannels, newScope).map(
      (locale: Locale) => locale.code
    );
    const newFilter = {...filter, context: {scope: newScope, locales: newAvailableLocaleCodes}};
    onChange(newFilter);
  };
  const handleQualityScoreChange = (newQualityScores: QualityScores) => {
    if (0 === newQualityScores.length) {
      const resetFilter = {...filter, operator: null, value: []};
      onChange(resetFilter);
    } else {
      const newScope = filter.context?.scope ?? availableChannels[0].code;
      const newLocales =
        filter.context?.locales ??
        getLocalesFromChannel(availableChannels, newScope).map((locale: Locale) => locale.code);
      const newOperator = filter.operator ?? 'IN AT LEAST ONE LOCALE';

      const newFilter = {
        ...filter,
        value: newQualityScores.map((qualityScore: QualityScore) => AVAILABLE_QUALITY_SCORES.indexOf(qualityScore) + 1),
        operator: newOperator,
        context: {
          scope: newScope,
          locales: newLocales,
        },
      };
      onChange(newFilter);
    }
  };

  return (
    <Container>
      <QualityScoreSelector
        availableQualityScores={AVAILABLE_QUALITY_SCORES}
        qualityScores={filter.value.map((qualityScore: number) => AVAILABLE_QUALITY_SCORES[qualityScore - 1])}
        onChange={handleQualityScoreChange}
        validationErrors={valueErrors}
      />
      {null !== filter.value && null !== filter.operator && (
        <>
          <OperatorSelector
            availableOperators={availableOperators}
            operator={filter.operator}
            onChange={handleOperatorChange}
            validationErrors={operatorErrors}
          />
          <ChannelDropdown
            value={filter.context?.scope ?? ''}
            channels={availableChannels ?? []}
            validationErrors={scopeErrors}
            onChange={handleChannelChange}
          />
          <LocalesSelector
            value={filter.context?.locales ?? []}
            locales={availableLocales ?? []}
            onChange={handleLocalesChange}
            validationErrors={localesErrors}
            label={translate('akeneo.tailored_export.filters.quality_score.locales.label')}
            placeholder={translate('akeneo.tailored_export.filters.quality_score.locales.placeholder')}
            removeLabel={translate('akeneo.tailored_export.filters.quality_score.locales.remove')}
          />
        </>
      )}
    </Container>
  );
};

export {QualityScoreFilter};
