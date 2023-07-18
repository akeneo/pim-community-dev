import React, {ReactNode, isValidElement} from 'react';
import {useTranslate} from './useTranslate';

type ComponentFunction = (innerText: string) => ReactNode;
type ComponentsOrStrings = (string | number | ReactNode)[];

const replaceComponentPlaceholder = (
  elements: ComponentsOrStrings,
  componentName: string,
  componentFunction: ComponentFunction
): ComponentsOrStrings => {
  const result: ComponentsOrStrings = [];

  elements.forEach(element => {
    if (typeof element !== 'string') {
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
        const component = componentFunction(middle)
        if (isValidElement(component)) {
          result.push(React.cloneElement(component, {key: componentName}));
        }
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

  return (
    id: string,
    componentPlaceholders: {[componentName: string]: ComponentFunction},
    placeholders?: {[name: string]: string | number},
    count?: number
  ): ReactNode => {
    const basicTranslation = baseTranslate(id, placeholders, count);

    let elements: ComponentsOrStrings = [basicTranslation];
    Object.keys(componentPlaceholders).forEach(componentName => {
      elements = replaceComponentPlaceholder(elements, componentName, componentPlaceholders[componentName]);
    });

    return <>{elements}</>;
  };
};

export {useTranslateWithComponents};
