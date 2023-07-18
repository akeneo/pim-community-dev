import React, {ReactElement} from 'react';
import {useTranslate} from './useTranslate';

type ComponentFunction = (innerText: string) => ReactElement<any, any>;
type ComponentsOrStrings = (string | number | ReactElement<any, any>)[];

const splitPreviousElements: (
  previousElements: ComponentsOrStrings,
  componentName: string,
  componentFunction: ComponentFunction
) => ComponentsOrStrings = (previousElements, componentName, value) => {
  const result: ComponentsOrStrings = [];

  previousElements.forEach(element => {
    if (typeof element !== 'string' && typeof element !== 'number') {
      result.push(element);
    } else {
      // The regexp matches text like "my left text <em>my middle text</em> my right text"
      const regex = new RegExp(`(?<left>.*)<${componentName}>(?<middle>.*)<\/${componentName}>(?<right>.*)`);
      const matches = String(element).match(regex);
      if (matches?.groups) {
        const left = matches.groups.left;
        const right = matches.groups.right;
        const middle = matches.groups.middle;

        if (left !== '') result.push(left);
        result.push(React.cloneElement(value(middle), {componentName}));
        if (right !== '') result.push(right);
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
    componentPlaceholders: {[componentName: string]: ComponentFunction},
    placeholders?: {[name: string]: string | number},
    count?: number
  ) => ReactElement<any, any> = (id, componentPlaceholders, placeholders, count) => {
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
