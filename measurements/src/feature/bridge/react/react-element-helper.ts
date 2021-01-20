import ReactDOM from 'react-dom';

let container: Element | null = null;

const mountReactElementRef = (component: JSX.Element) => {
  if (null === container) {
    container = document.createElement('div');
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
