import ReactDOM from 'react-dom';

let container: Element | null = null;

export const mountReactElementRef = (component: JSX.Element) => {
  if (null === container) {
    container = document.createElement('div');
    ReactDOM.render(component, container);
  }

  return container;
};

export const unmoundReactElementRef = () => {
  if (null !== container) {
    ReactDOM.unmountComponentAtNode(container);
    container = null;
  }
};
