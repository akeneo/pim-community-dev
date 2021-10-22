import React from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {AttributeCode} from '../models';

type BackboneFieldElement = {
  render: () => {
    el: HTMLElement;
  };
};
type HTMLFieldElement = string;
type jQueryFieldElement = HTMLElement[];

export type ProductFieldElement = BackboneFieldElement | HTMLFieldElement | jQueryFieldElement;

const useRenderElements = (
  attributeCode: AttributeCode,
  elements: {[position: string]: {[elementKey: string]: ProductFieldElement}}
) => {
  const isBackboneModule = (element: ProductFieldElement) =>
    typeof element === 'object' && 'render' in element && typeof element.render === 'function';
  const isHTML = (element: ProductFieldElement) => typeof element === 'string';
  const Router = useRouter();

  /**
   * The Component rendering this element contains links done with jQuery. In React, we can't get recover them.
   * To avoid rewriting all the Field component, we added this method.
   * Instead of rendering the element in the same way than the other, we add logic in it.
   * The rendered component with links looks like "This attribute can be updated by <span>2 rules</span>".
   * We re-add manually the link to the span element.
   *
   * @see src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/product/form/attributes/smart-attribute.js
   */
  const renderRulesElement = (elementKey: string, element: jQueryFieldElement) => {
    const innerHTML = element[0].innerHTML;

    const matches = /^(?<left>.*)<span>(?<link>.*)<\/span>(?<right>.*)$/im.exec(innerHTML);
    return matches && matches.groups ? (
      <span key={elementKey}>
        <div className='from-smart'>
          {matches.groups.left}
          <span
            onClick={() => {
              sessionStorage.setItem('current_form_tab', 'pim-attribute-edit-form-rules-tab');
              const route = Router.generate('pim_enrich_attribute_edit', {code: attributeCode});
              Router.redirect(route);
            }}
          >
            {matches.groups.link}
          </span>
          {matches.groups.right}
        </div>
      </span>
    ) : null;
  };

  const renderElement = (position: string, elementKey: string) => {
    const element = elements[position][elementKey];

    if (
      position === 'footer' &&
      elementKey === 'from_smart' &&
      renderRulesElement(elementKey, element as jQueryFieldElement)
    ) {
      return renderRulesElement(elementKey, element as jQueryFieldElement);
    } else if (isBackboneModule(element)) {
      return (
        <span
          key={elementKey}
          dangerouslySetInnerHTML={{__html: (element as BackboneFieldElement).render().el.innerHTML}}
        />
      );
    } else if (isHTML(element)) {
      return <span key={elementKey} dangerouslySetInnerHTML={{__html: element as HTMLFieldElement}} />;
    } else {
      return <span key={elementKey} dangerouslySetInnerHTML={{__html: (element as jQueryFieldElement)[0].outerHTML}} />;
    }
  };

  const renderElements: (position: string) => React.ReactNode = position => {
    return <>{Object.keys(elements[position] || []).map(elementKey => renderElement(position, elementKey))}</>;
  };

  return renderElements;
};

export {useRenderElements};
