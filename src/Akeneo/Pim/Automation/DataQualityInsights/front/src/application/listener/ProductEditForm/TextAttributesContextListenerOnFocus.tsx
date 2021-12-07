import React, {useCallback, useEffect, useLayoutEffect, useMemo, useState} from 'react';
import {useDispatch} from 'react-redux';
import {usePageContext, useProduct, useProductFamily} from '../../../infrastructure/hooks';
import {createWidget, EditorElement, WidgetsCollection} from '../../helper';
import {Product} from '../../../domain';
import {Attribute, Family} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {initializeWidgetsListAction} from '../../../infrastructure/reducer';
import useFetchActiveLocales from '../../../infrastructure/hooks/EditorHighlight/useFetchActiveLocales';
import {useAttributeGroupsStatusContext} from '@akeneo-pim-community/data-quality-insights/src/application';
import {AttributeGroupsStatusCollection} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks';

const uuidV5 = require('uuid/v5');

const WIDGET_UUID_NAMESPACE = '4e34f5c2-d1b0-4cf2-96c9-dca6b95e695e';
const PRODUCT_ATTRIBUTES_CONTAINER_SELECTOR = '.entity-edit-form.edit-form div[data-drop-zone="container"]';
const EDITOR_ELEMENT_SELECTOR = ['.field-input textarea', '.field-input input[type="text"]'].join(', ');
const RICH_EDITOR_ELEMENT_SELECTOR = ['.field-input div.note-editable[contenteditable]'].join(', ');

export const getTextAttributes = (family: Family, product: Product, activeLocalesNumber: number) => {
  const isValidTextarea = (attribute: Attribute) =>
    attribute.type === 'pim_catalog_textarea' &&
    (attribute.localizable || activeLocalesNumber === 1) &&
    !attribute.is_read_only &&
    !attribute.wysiwyg_enabled;
  const isValidText = (attribute: Attribute) =>
    attribute.type === 'pim_catalog_text' &&
    (attribute.localizable || activeLocalesNumber === 1) &&
    !attribute.is_read_only;
  const isVariantProductOrSubProductModel = (product: Product) => product.meta.level !== null;
  const isAttributeEditable = (attribute: Attribute, product: Product) =>
    product.meta.attributes_for_this_level.includes(attribute.code);
  const isValidRichTextarea = (attribute: Attribute) =>
    attribute.type === 'pim_catalog_textarea' &&
    attribute.localizable &&
    !attribute.is_read_only &&
    attribute.wysiwyg_enabled;

  return family.attributes.filter(attribute => {
    return (
      (isValidTextarea(attribute) || isValidRichTextarea(attribute) || isValidText(attribute)) &&
      (!isVariantProductOrSubProductModel(product) ||
        (isVariantProductOrSubProductModel(product) && isAttributeEditable(attribute, product)))
    );
  });
};

export const isValidTextAttributeElement = (
  element: Element | null,
  attributes: Attribute[],
  attributeGroupsStatus: AttributeGroupsStatusCollection
) => {
  if (!element || !element.hasAttribute('data-attribute')) {
    return false;
  }
  const attributeCode = element.getAttribute('data-attribute');
  const attribute = attributes.find(attr => attr.code === attributeCode);
  const isValidTextarea = (attribute: Attribute) =>
    attribute.type === 'pim_catalog_textarea' && !attribute.wysiwyg_enabled;
  const isValidRichTextarea = (attribute: Attribute) =>
    attribute.type === 'pim_catalog_textarea' && attribute.wysiwyg_enabled;
  const isValidText = (attribute: Attribute) => attribute.type === 'pim_catalog_text';
  const isAttributeGroupEnabled = (attribute: Attribute) => attributeGroupsStatus[attribute.group] || false;

  return (
    attribute &&
    (isValidTextarea(attribute) || isValidRichTextarea(attribute) || isValidText(attribute)) &&
    isAttributeGroupEnabled(attribute)
  );
};

const getEditorElement = (element: Element) => {
  let editor = element.querySelector(RICH_EDITOR_ELEMENT_SELECTOR);
  let editorId: string | null = null;

  if (editor) {
    const hiddenEditor = element.querySelector(EDITOR_ELEMENT_SELECTOR);
    editorId = hiddenEditor !== null ? hiddenEditor.id : null;
  }

  if (!editor) {
    editor = element.querySelector(EDITOR_ELEMENT_SELECTOR);
    editorId = editor !== null ? editor.id : null;
  }

  return {
    editor,
    editorId,
  };
};

const TextAttributesContextListenerOnFocus = () => {
  const family = useProductFamily();
  const {attributesTabIsLoading} = usePageContext();
  const product = useProduct();
  const dispatchAction = useDispatch();
  const activeLocales = useFetchActiveLocales();
  const {status: attributeGroupsStatus, load} = useAttributeGroupsStatusContext();
  const textAttributes = useMemo(() => {
    if (!family || activeLocales.length === 0) {
      return [];
    }
    return getTextAttributes(family, product, activeLocales.length)
  }, [family, product, activeLocales]);
  const [widgetsList, setWidgetsList] = useState<WidgetsCollection>({});

  const addWidget = useCallback((container: Element, element: Element) => {
    if (!isValidTextAttributeElement(element, textAttributes, attributeGroupsStatus)) {
      return;
    }

    const attribute = element.getAttribute('data-attribute');
    const {editor, editorId} = getEditorElement(element);

    if (!attribute || !editor || widgetsList[attribute]) {
      return;
    }

    setWidgetsList((list) => {
      const widgetId = uuidV5(`${product.meta.id}-${attribute}`, WIDGET_UUID_NAMESPACE);
      return {
        ...list,
        [attribute]:  createWidget(widgetId, editor as EditorElement, editorId, attribute),
      }
    });
  }, [widgetsList, textAttributes, attributeGroupsStatus]);

  useEffect(() => {
    load();
  }, []);

  useEffect(() => {
    if (Object.entries(widgetsList).length === 0) {
      return;
    }

    dispatchAction(initializeWidgetsListAction(widgetsList));
  }, [widgetsList]);

  useLayoutEffect(() => {
    const container = document.querySelector(PRODUCT_ATTRIBUTES_CONTAINER_SELECTOR);
    if (!container || textAttributes.length === 0) {
      return;
    }

    const handleFocus = (event: Event) => {
      const inputField = event.target as Element;
      const element = inputField.closest('[data-attribute]');

      if (!element) {
        return;
      }

      addWidget(container, element)
    };

    container.addEventListener('focus', handleFocus as EventListener, true);
    return () => {
      container.removeEventListener('focus', handleFocus as EventListener, true);
    }

  }, [attributesTabIsLoading, textAttributes, addWidget]);

  return <></>;
};

export {TextAttributesContextListenerOnFocus};
