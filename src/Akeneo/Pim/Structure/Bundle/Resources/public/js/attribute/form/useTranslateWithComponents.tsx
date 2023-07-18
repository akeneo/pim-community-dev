import React, {ReactElement} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';

type ComponentFunction = (innerText: string) => ReactElement<any, any>;
type ComponentsOrStrings = (string | ReactElement<any, any>)[];

const splitPreviousElements: (
  previousElements: ComponentsOrStrings,
  componentName: string,
  componentFunction: ComponentFunction
) => ComponentsOrStrings = (previousElements, key, value) => {
  const result: ComponentsOrStrings = [];

  previousElements.forEach(element => {
    if (typeof element !== 'string') {
      result.push(element);
    } else {
      // The regexp matches text like "my left text <em>my middle text</em> my right text"
      const regex = new RegExp(`(?<left>.*)<${key}>(?<middle>.*)<\/${key}>(?<right>.*)`);
      const matches = element.match(regex);
      if (matches?.groups) {
        const left = matches.groups.left;
        const right = matches.groups.right;
        const middle = matches.groups.middle;

        result.push(left, React.cloneElement(value(middle), {key}), right);
      } else {
        result.push(element);
      }
    }
  });

  return result;
};

const useTranslateWithComponents = () => {
  const baseTranslate = useTranslate();
  const translateWithComponents: (
    id: string,
    placeholders?: {[name: string]: string | number},
    count?: number,
    componentPlaceholders?: {[componentName: string]: ComponentFunction}
  ) => ReactElement<any, any> = (id, placeholders, count, componentPlaceholders = {}) => {
    const basicTranslation = baseTranslate(id, placeholders, count);

    let elements: ComponentsOrStrings = [basicTranslation];
    Object.keys(componentPlaceholders).forEach(componentName => {
      elements = splitPreviousElements(elements, componentName, componentPlaceholders[componentName]);
    });

    return <>{elements}</>;
  };

  return translateWithComponents;
};

export {useTranslateWithComponents};
