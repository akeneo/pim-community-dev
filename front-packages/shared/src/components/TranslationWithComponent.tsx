import React, {ReactNode} from 'react';
import {useTranslate} from '../hooks/useTranslate';

type TranslationWithComponentProps = {
  id: string;
  parameters?: {[name: string]: string | number};
  count?: number;
  components: {[key: string]: ReactNode};
}

const TranslationWithComponent = ({id, parameters, count, components}: TranslationWithComponentProps): any => {
  const translate = useTranslate();

  const regex = /<([A-Za-z0-9]*)>(.*)<\/\1>/g;
  const translation = translate(id, parameters, count);
  const output: Array<(string | JSX.Element)> = [];

  let result = regex.exec(translation);
  let firstRegexIndex = 0;

  while (result !== null) {
    const matchStartAt = result.index;
    const matchedContent = result[0];
    const tagName = result[1];
    const tagContent = result[2];

    const contentBeforeMatch = translation.substring(firstRegexIndex, matchStartAt);
    output.push(contentBeforeMatch);

    const matchedComponent = components[tagName];
    output.push(React.isValidElement(matchedComponent) ?
      React.cloneElement(matchedComponent, {key: result.index, children: tagContent}) :
      matchedContent
    );

    firstRegexIndex = regex.lastIndex + 1;
    result = regex.exec(translation);
  }

  if (firstRegexIndex < translation.length) {
    output.push(translation.substring(firstRegexIndex, translation.length - 1));
  }

  return output;
};

export {TranslationWithComponent};
