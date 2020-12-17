import React, {Ref} from 'react';
import styled from 'styled-components';
import {getFontSize} from '../../theme';
import Flag from 'react-country-flag';

const LocaleContainer = styled.span`
  display: inline-flex;
`;

const Emoji = styled.span`
  font-size: ${getFontSize('bigger')};
  margin-right: 5px;
  align-items: center;
  display: inline-flex;
`;

type LocaleProps = {
  /**
   * Code of the locale (eg: en_US, fr_FR, etc).
   */
  code: string;

  /**
   * Override the language label (English instead of en). Fallback to language code if empty.
   */
  languageLabel?: string;
};

/**
 * Component to display a locale (country and language).
 */
const Locale = React.forwardRef<HTMLSpanElement, LocaleProps>(
  ({code, languageLabel, ...rest}: LocaleProps, forwardedRef: Ref<HTMLSpanElement>) => {
    const {0: languageCode, length, [length - 1]: countryCode} = code.split('_');

    return (
      <LocaleContainer ref={forwardedRef} {...rest}>
        <Emoji role="img" aria-label={countryCode}>
          <Flag countryCode={countryCode} svg={navigator.userAgent.indexOf('Windows') !== -1} />
        </Emoji>
        {languageLabel || languageCode}
      </LocaleContainer>
    );
  }
);

export {Locale};
export type {LocaleProps};
