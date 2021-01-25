import React, {FC, useCallback, useEffect, useState} from 'react';
import {ElementsList} from '../../../../infrastructure/hooks/AttributeEditForm/useSpellcheckOptionsListState';
import useSpellcheckPopoverState, {
  useSpellcheckPopoverProps,
} from '../../../../infrastructure/hooks/Common/Spellcheck/useSpellcheckPopoverState';
import SpellcheckContentContextProvider from '../../../context/Spellcheck/SpellcheckContentContext';
import SpellcheckElement from '../../Common/HighlightableContent/Spellcheck/SpellcheckElement';
import UpdateHighlightsOnInputChange from '../../Common/HighlightableContent/UpdateHighlightsOnInputChange';
import SpellcheckPopoverDisclosure from '../../Common/HighlightableContent/Spellcheck/SpellcheckPopoverDisclosure';
import SpellcheckPopover from '../../Common/HighlightableContent/Spellcheck/SpellcheckPopover';
import {
  fetchIgnoreTextIssue,
  useCatalogContext,
  useGetEditorHighlightWidgetsList,
  useProduct,
} from '../../../../infrastructure';
import {EditorElement} from '../../../helper';
import ActiveHighlightsOnFocus from '../../Common/HighlightableContent/ActiveHighlightsOnFocus';
import withPortal from '../../Common/Decorator/withPortal';
import {isSimpleProduct, isVariantProduct} from '@akeneo-pim-community/data-quality-insights/src/application/helper';
import fetchProductModelIgnoreTextIssue from '../../../../infrastructure/fetcher/ProductEditForm/Spellcheck/fetchProductModelIgnoreTextIssue';
import applySuggestionOnContent from '../../../helper/ProductEditForm/Spellcheck/applySuggestionOnContent';

const SPELLCHECK_PRODUCT_VALUE_ELEMENT_BASE_ID = 'product-value-spellcheck';
const SPELLCHECK_PRODUCT_VALUE_ELEMENT_CONTAINER_ID = 'product-value-spellcheck-container';

const Container: FC = ({children}) => <div>{children}</div>;
const SpellcheckPopoverContainer = withPortal(Container);

const SpellcheckProductValuesList: FC = () => {
  const widgets = useGetEditorHighlightWidgetsList();
  const {locale} = useCatalogContext();
  const product = useProduct();
  const [elements, setElements] = useState<ElementsList<EditorElement>>({});

  useEffect(() => {
    let list: ElementsList<EditorElement> = {};
    Object.entries(widgets).map(([widgetId, widget]) => {
      list = {
        ...list,
        [widgetId]: {
          element: widget.editor,
          locale: locale as string,
        },
      };
    });
    setElements(list);
  }, [widgets]);

  const handleIgnore = useCallback(
    (text: string, locale: string) => {
      if (!product) {
        return;
      }

      (async () => {
        if (isSimpleProduct(product) || isVariantProduct(product)) {
          await fetchIgnoreTextIssue(text, locale, product.meta.id as number);
        } else {
          await fetchProductModelIgnoreTextIssue(text, locale, product.meta.id as number);
        }

        Object.values(elements).forEach(({element}) => {
          element.dispatchEvent(new Event('input', {bubbles: true}));
        });
      })();
    },
    [product, elements]
  );

  const popoverState = useSpellcheckPopoverState({
    apply: applySuggestionOnContent,
    ignore: handleIgnore,
  });
  const popoverProps = useSpellcheckPopoverProps(popoverState);

  return (
    <>
      {Object.entries(elements).map(([key, {locale, element}]) => (
        <SpellcheckContentContextProvider key={key} locale={locale} element={element}>
          <SpellcheckElement baseId={SPELLCHECK_PRODUCT_VALUE_ELEMENT_BASE_ID}>
            <ActiveHighlightsOnFocus />
            <UpdateHighlightsOnInputChange />
            <SpellcheckPopoverDisclosure element={element} {...popoverState} />
          </SpellcheckElement>
        </SpellcheckContentContextProvider>
      ))}

      <SpellcheckPopoverContainer
        rootElement={document.body}
        containerId={SPELLCHECK_PRODUCT_VALUE_ELEMENT_CONTAINER_ID}
      >
        <SpellcheckPopover {...popoverProps} modal={false} />
      </SpellcheckPopoverContainer>
    </>
  );
};

export default SpellcheckProductValuesList;
