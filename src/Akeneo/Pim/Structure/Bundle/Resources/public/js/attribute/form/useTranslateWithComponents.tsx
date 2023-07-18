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
    if (typeof element !== 'string' && typeof element !== 'number') {
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

function splitComponents(placeholders: {[p: string]: string | number | ComponentFunction}) {
  type OriginalPlaceholders = {[name: string]: string | number};
  type ComponentPlaceholders = {[componentName: string]: ComponentFunction};

  return Object.keys(placeholders).reduce<{
    basePlaceholders: OriginalPlaceholders;
    componentPlaceholders: ComponentPlaceholders;
  }>(
    (previous, name) => {
      const basePlaceholders = previous.basePlaceholders;
      const componentPlaceholders = previous.componentPlaceholders;
      if (typeof placeholders[name] === 'string' || typeof placeholders[name] === 'number') {
        basePlaceholders[name] = placeholders[name] as string | number;
      } else {
        componentPlaceholders[name] = placeholders[name] as ComponentFunction;
      }

      return {basePlaceholders: basePlaceholders, componentPlaceholders};
    },
    {basePlaceholders: {}, componentPlaceholders: {}}
  );
}

const useTranslateWithComponents = () => {
  const baseTranslate = useTranslate();
  const translateWithComponents: (
    id: string,
    placeholders?: {[name: string]: string | number | ComponentFunction},
    count?: number
  ) => ReactElement<any, any> = (id, placeholders, count) => {
    const {basePlaceholders, componentPlaceholders} = splitComponents(placeholders || {});

    const basicTranslation = baseTranslate(id, basePlaceholders, count);

    let elements: ComponentsOrStrings = [basicTranslation];
    Object.keys(componentPlaceholders).forEach(componentName => {
      elements = splitPreviousElements(elements, componentName, componentPlaceholders[componentName]);
    });

    return <>{elements}</>;
  };

  return translateWithComponents;
};

export {useTranslateWithComponents};
