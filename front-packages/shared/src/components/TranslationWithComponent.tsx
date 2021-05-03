import React, {ReactNode} from 'react';
import {useTranslate} from '../hooks/useTranslate';

type TranslationWithComponentProps = {
  id: string;
  components: {[key: string]: ReactNode}
}

const TranslationWithComponent = ({id, components}: TranslationWithComponentProps): any => {
  const regex = /<([A-Za-z0-9]*)>(.*)<\/\1>/g;
  const translate = useTranslate();

  const translation = translate(id);
  const output: Array<(string | JSX.Element)> = [];

  let processedInput = translation;
  let result = regex.exec(processedInput);
  while (result !== null) {
    const matchStartAt = result.index;
    const match = result[0];

    const contentBeforeMatch: string = processedInput.substring(0, matchStartAt);
    output.push(contentBeforeMatch);

    const matchedComponent = components[result[1]];
    if (React.isValidElement(matchedComponent)) {
      output.push(React.cloneElement(matchedComponent, {key: result.index, children: result[2]}));
    }

    processedInput = processedInput.substring(matchStartAt + match.length, processedInput.length + 1);
    regex.lastIndex = 0;
    result = regex.exec(processedInput);
  }

  if (processedInput) {
    output.push(processedInput);
  }

  return output;
};

export {TranslationWithComponent};
