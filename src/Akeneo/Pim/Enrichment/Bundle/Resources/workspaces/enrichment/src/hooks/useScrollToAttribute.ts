import {useEffect, useLayoutEffect, useState} from 'react';
import {scrollToAttribute} from '../helpers';
import {Product} from '../models';
import {useMediator} from '@akeneo-pim-community/legacy-bridge';

const useScrollToAttribute = (product: Product) => {
  const [attributesLoaded, setAttributesLoaded] = useState(true);
  const [attributeToScrollTo, setAttributeToScrollTo] = useState<string | null>(null);
  const [isAttributeDisplayed, setIsAttributeDisplayed] = useState(false);
  const mediator = useMediator();

  useEffect(() => {
    const attributesLoadingHandler = () => setAttributesLoaded(false);
    mediator.on('ATTRIBUTES_LOADING', attributesLoadingHandler);
    const attributesLoadedHandler = () => setAttributesLoaded(true);
    mediator.on('ATTRIBUTES_LOADED', attributesLoadedHandler);

    if (sessionStorage.getItem('attributeToScrollTo')) {
      setAttributeToScrollTo(sessionStorage.getItem('attributeToScrollTo'));
      sessionStorage.removeItem('attributeToScrollTo');
    }

    return () => {
      mediator.off('ATTRIBUTES_LOADING', attributesLoadingHandler);
      mediator.off('ATTRIBUTES_LOADED', attributesLoadedHandler);
    };
  }, []);

  useLayoutEffect(() => {
    const container = document.querySelector('.entity-edit-form.edit-form div[data-drop-zone="container"]');
    let observer: MutationObserver | null = null;

    if (container && attributeToScrollTo) {
      observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          const element = mutation.target as Element;
          const attributeCode = element.getAttribute('data-attribute');

          if (attributeCode && attributeCode === attributeToScrollTo) {
            setIsAttributeDisplayed(true);
          }
        });
      });

      observer.observe(container, {
        childList: true,
        subtree: true,
      });
    }

    return () => {
      if (observer) {
        observer.disconnect();
      }
    };
  }, [product, attributesLoaded, attributeToScrollTo]);

  useLayoutEffect(() => {
    if (attributesLoaded && attributeToScrollTo && isAttributeDisplayed) {
      // Add delay to ensure that all attributes are rendered
      setTimeout(() => {
        scrollToAttribute(attributeToScrollTo);
      }, 200);
      setAttributeToScrollTo(null);
      setIsAttributeDisplayed(false);
    }
  }, [attributesLoaded, attributeToScrollTo, isAttributeDisplayed]);

  return {
    setAttributeToScrollTo,
    setIsAttributeDisplayed,
  };
};

export {useScrollToAttribute};
