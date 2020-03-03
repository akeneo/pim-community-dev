import React, {useLayoutEffect} from "react";
import {useDispatch} from "react-redux";
import {useFetchProductFamilyInformation, usePageContext, useProduct} from "../../infrastructure/hooks";
import {createWidget, EditorElement, WidgetsCollection} from "../helper";
import {Attribute, Family, Product} from "../../domain";
import {initializeWidgetsListAction} from "../../infrastructure/reducer";

const uuidV5 = require('uuid/v5');

const WIDGET_UUID_NAMESPACE = '4e34f5c2-d1b0-4cf2-96c9-dca6b95e695e';

export const getTextAttributes = (family: Family, product: Product) => {
  const isValidTextarea = (attribute: Attribute) => (attribute.type === "pim_catalog_textarea" && attribute.localizable && !attribute.is_read_only && !attribute.wysiwyg_enabled);
  const isValidText = (attribute: Attribute) => (attribute.type === "pim_catalog_text" && attribute.localizable && !attribute.is_read_only);
  const isVariantProduct = (product: Product) => product.meta.level !== null;
  const isAttributeEditable = (attribute: Attribute, product: Product) => product.meta.attributes_for_this_level.includes(attribute.code);

  return family.attributes.filter((attribute) => {
    return (
      isValidTextarea(attribute) ||
      isValidText(attribute)
    ) && (!isVariantProduct(product) || (isVariantProduct(product) && isAttributeEditable(attribute, product)));
  });
};

const isTextAttributeElement = (element: Element | null, attributes: Attribute[]) => {
  if (!element || !element.hasAttribute('data-attribute')) {
    return false;
  }
  const attributeCode = element.getAttribute('data-attribute');
  const attribute = attributes.find(attr => attr.code === attributeCode);
  const isValidTextarea = (attribute: Attribute) => (attribute.type === 'pim_catalog_textarea' && !attribute.wysiwyg_enabled);
  const isValidText = (attribute: Attribute) =>  (attribute.type === 'pim_catalog_text');

  return attribute && (
    isValidTextarea(attribute) || isValidText(attribute)
  );
};

const TextAttributesContextListener = () => {
  const family = useFetchProductFamilyInformation();
  const {attributesTabIsLoading} = usePageContext();
  const product = useProduct();
  const dispatchAction = useDispatch();

  useLayoutEffect(() => {
    const container = document.querySelector('.entity-edit-form.edit-form div[data-drop-zone="container"]');
    let observer: MutationObserver|null = null;

    if (family && container) {
      const textAttributes = getTextAttributes(family, product);
      observer = new MutationObserver((mutations) => {
        let widgetList: WidgetsCollection = {};
        mutations.forEach((mutation) => {
          if (isTextAttributeElement(mutation.target as Element, textAttributes)) {
            const element = mutation.target as Element;
            const attribute = element.getAttribute('data-attribute');
            const editor = element.querySelector('.field-input textarea, .field-input input[type="text"]'); // @todo adapt for contenteditable and input text elements

            if (!attribute || !editor) {
              return;
            }

            editor.setAttribute('data-gramm', 'false');
            editor.setAttribute('data-gramm_editor', 'false');
            editor.setAttribute("spellcheck", 'false');

            const widgetId = uuidV5(`${product.meta.id}-${attribute}`, WIDGET_UUID_NAMESPACE);
            const isMainLabel = (attribute === family.attribute_as_label);
            widgetList[widgetId] = createWidget(widgetId, editor as EditorElement, attribute, isMainLabel);
          }
        });

        if (Object.entries(widgetList).length > 0) {
          dispatchAction(initializeWidgetsListAction(widgetList));
        }
      });

      observer.observe(container, {
        childList: true,
        subtree: true
      });
    }

    return () => {
      if (observer) {
        observer.disconnect();
      }
    }
  },
  [product, family, attributesTabIsLoading]);

  return (
    <></>
  );
};

export default TextAttributesContextListener;
