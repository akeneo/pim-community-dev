import {useLayoutEffect, useReducer, useState} from 'react';
import {
  addVisibleAttributeOptionAction,
  removeVisibleAttributeOptionAction,
  visibleAttributeOptionsReducer,
} from '../../reducer/AttributeEditForm/visibleAttributeOptionsReducer';

export const useVisibleAttributeOptions = () => {
  const [visibleOptions, dispatch] = useReducer(visibleAttributeOptionsReducer, []);
  const [renderingCount, setRenderingCount] = useState<number>(0);

  useLayoutEffect(() => {
    const container = document.querySelector('.edit-form');
    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        const element = mutation.target as Element;
        if (
          element.hasAttribute('data-testid') &&
          element.getAttribute('data-testid') === 'attribute-options-list' &&
          element.querySelectorAll('tr[data-testid="attribute-option-item"]').length > 0
        ) {
          setRenderingCount(renderingCount + 1);
        }
      });
    });

    if (container) {
      observer.observe(container, {
        childList: true,
        subtree: true,
      });
    }

    return () => {
      observer.disconnect();
    };
  }, []);

  useLayoutEffect(() => {
    const elements = document.querySelectorAll('tr[data-testid="attribute-option-item"]');
    const container = document.querySelector('div[data-testid="attribute-options-list"]');

    const observer = new IntersectionObserver(
      function(entries) {
        entries.forEach(entry => {
          const codeElement = entry.target.querySelector('[data-testid="attribute-option-item-code"]');
          if (!codeElement) {
            return;
          }
          const option = codeElement.textContent;

          if (!option) {
            return;
          }

          if (entry.isIntersecting) {
            dispatch(addVisibleAttributeOptionAction(option));
          } else {
            dispatch(removeVisibleAttributeOptionAction(option));
          }
        });
      },
      {
        root: container,
        rootMargin: '350px 0px',
      }
    );

    elements.forEach(element => {
      observer.observe(element);
    });

    return () => {
      elements.forEach(element => {
        observer.unobserve(element);
      });
      observer.disconnect();
    };
  }, [renderingCount]);

  return {
    visibleOptions,
  };
};
