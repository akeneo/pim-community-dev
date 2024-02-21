import React, {useLayoutEffect, useState} from 'react';
import {useDispatch} from 'react-redux';
import {useProductFamily, usePageContext, useProduct} from '../../../infrastructure/hooks';
import {showDataQualityInsightsAttributeToImproveAction} from '../../../infrastructure/reducer';

const handleScrollToAttribute = (attribute: string) => {
  const form = document.querySelector('.edit-form');
  const attributeElement = document.querySelector(`.field-container[data-attribute= ${attribute}]`);
  const DEFAULT_SCROLL_TOP_MARGIN = 5;

  if (!form || !attributeElement) {
    return;
  }

  let scrollTopMargin = DEFAULT_SCROLL_TOP_MARGIN;
  const header = form.querySelector('header.navigation');
  const actions = form.querySelector('header.attribute-actions');
  const stickySectionTitle = form.querySelector('.AknSubsection-title[style*="sticky"]');
  const attributeTopPosition = attributeElement.getBoundingClientRect().top;

  if (header) {
    scrollTopMargin += header.getBoundingClientRect().height;
  }

  if (actions) {
    scrollTopMargin += actions.getBoundingClientRect().height;
  }

  if (stickySectionTitle) {
    scrollTopMargin += stickySectionTitle.getBoundingClientRect().height;
  }

  form.scrollTo({
    top: attributeTopPosition - scrollTopMargin,
    behavior: 'smooth',
  });

  handleFocusOnAttribute(attributeElement);
};

const handleFocusOnAttribute = (attribute: Element) => {
  const fieldInput =
    attribute.querySelector('.field-input div.note-editable') ||
    attribute.querySelector('.field-input input, .field-input textarea');

  if (fieldInput) {
    // @ts-ignore
    fieldInput.focus({preventScroll: true});
  }
};

const AttributeToImproveContextListener = () => {
  const family = useProductFamily();
  const {attributesTabIsLoading, attributeToImprove} = usePageContext();
  const product = useProduct();
  const [isAttributeDisplayed, setIsAttributeDisplayed] = useState(false);
  const dispatchAction = useDispatch();

  useLayoutEffect(() => {
    if (attributeToImprove && isAttributeDisplayed) {
      const attributeElement = document.querySelector(`.field-container[data-attribute= ${attributeToImprove}]`);

      if (attributeElement !== null) {
        handleScrollToAttribute(attributeToImprove);
        setIsAttributeDisplayed(false);
        dispatchAction(showDataQualityInsightsAttributeToImproveAction(null));
      }
    }
  }, [attributeToImprove, isAttributeDisplayed]);

  useLayoutEffect(() => {
    const container = document.querySelector('.entity-edit-form.edit-form div[data-drop-zone="container"]');
    let observer: MutationObserver | null = null;

    if (family && container) {
      if (attributeToImprove) {
        observer = new MutationObserver(mutations => {
          mutations.forEach(mutation => {
            const element = mutation.target as Element;
            const attributeCode = element.getAttribute('data-attribute');

            if (attributeCode && attributeCode === attributeToImprove) {
              // Add delay to ensure that all attributes are rendered
              setTimeout(() => {
                setIsAttributeDisplayed(true);
              }, 200);
            }
          });
        });

        observer.observe(container, {
          childList: true,
          subtree: true,
        });
      }
    }

    return () => {
      if (observer) {
        observer.disconnect();
      }
    };
  }, [product, family, attributesTabIsLoading, attributeToImprove]);

  return <></>;
};

export default AttributeToImproveContextListener;
