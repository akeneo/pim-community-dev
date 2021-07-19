import React from 'react';
import styled from 'styled-components';
import {
  ChannelCode,
  filterErrors,
  formatParameters,
  getLocalesFromChannel,
  Locale,
  LocaleCode,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {Operator, OperatorSelector} from './OperatorSelector';
import {QualityScore, QualityScoreSelector} from './QualityScoreSelector';
import {ChannelDropdown} from '../ChannelDropdown';
import {useChannels} from '../../hooks';
import {LocalesSelector} from './LocalesSelector';

// For the margin-top/bottom, seen with Stephane until we have a better way
// of displaying dynamic/multi-lines fields
const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 15px;
  justify-content: space-between;
  width: 100%;
  margin-top: 60px;
  margin-bottom: 60px;
`;

type Filter = {
  field: 'quality_score_multi_locales';
  operator: Operator | null;
  value: number | null;
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

const AVAILABLE_QUALITY_SCORES = ['NO_CONDITION_ON_QUALITY_SCORE', 'A', 'B', 'C', 'D', 'E'];

const QualityScoreFilter = ({availableOperators, filter, onChange, validationErrors}: QualityScoreFilterProps) => {
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
  const handleQualityScoreChange = (newQualityScore: QualityScore) => {
    if ('NO_CONDITION_ON_QUALITY_SCORE' === newQualityScore) {
      const resetFilter = {...filter, operator: null, value: null};
      onChange(resetFilter);
    } else {
      const newScope = filter.context?.scope ?? availableChannels[0].code;
      const newLocales = getLocalesFromChannel(availableChannels, newScope).map((locale: Locale) => locale.code);

      const newFilter = {
        ...filter,
        operator: 'IN AT LEAST ONE LOCALE',
        value: AVAILABLE_QUALITY_SCORES.indexOf(newQualityScore),
        context: {
          locales: newLocales,
          scope: newScope,
        },
      };
      onChange(newFilter);
    }
  };

  return (
    <Container>
      <QualityScoreSelector
        availableQualityScores={AVAILABLE_QUALITY_SCORES}
        qualityScore={filter.value ? AVAILABLE_QUALITY_SCORES[filter.value] : AVAILABLE_QUALITY_SCORES[0]}
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
          />
        </>
      )}
    </Container>
  );
};

export {QualityScoreFilter};
