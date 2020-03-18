import ReactDOM from 'react-dom';

let container: Element | null = null;

const mountReactElementRef = (component: JSX.Element) => {
  if (null === container) {
    container = document.createElement('div');
    // (container as any).style.height = '100%';
    // (container as any).style.display = 'flex';
    // (container as any).style.flexDirection = 'column';
    ReactDOM.render(component, container);
  }

  return container;
};

const unmountReactElementRef = () => {
  if (null !== container) {
    ReactDOM.unmountComponentAtNode(container);
    container = null;
  }
};

export {mountReactElementRef, unmountReactElementRef};
