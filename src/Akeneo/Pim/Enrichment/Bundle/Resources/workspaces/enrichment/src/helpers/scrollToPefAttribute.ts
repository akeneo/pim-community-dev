const scrollToAttribute = (attribute: string) => {
  const form = document.querySelector('.edit-form');
  const attributeElement: HTMLElement | null = document.querySelector(`.field-container[data-attribute= ${attribute}]`);
  const DEFAULT_SCROLL_TOP_MARGIN = 5;

  if (!form || !attributeElement) {
    return;
  }

  let scrollTopMargin = DEFAULT_SCROLL_TOP_MARGIN;
  const header = form.querySelector('header.navigation');
  const actions = form.querySelector('header.attribute-actions');
  const stickySectionTitle = form.querySelector('.AknSubsection-title[style*="sticky"]');
  const attributeTopPosition = attributeElement.offsetTop;

  if (header) {
    scrollTopMargin += header.getBoundingClientRect().height;
  }

  if (actions) {
    scrollTopMargin += actions.getBoundingClientRect().height;
  }

  if (stickySectionTitle) {
    scrollTopMargin += stickySectionTitle.getBoundingClientRect().height;
  }

  if (!elementIsVisible(attributeElement)) {
    form.scrollTo({
      top: attributeTopPosition - scrollTopMargin,
      behavior: 'smooth',
    });
  }

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

function elementIsVisible(element: any) {
  const rect = element.getBoundingClientRect();

  return (
    rect.top >= 0 &&
    rect.top >= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight)
  );
}

export {scrollToAttribute};
