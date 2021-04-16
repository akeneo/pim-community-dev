import ReactDOM from 'react-dom';

const mountReactElementRef = (component: JSX.Element, container: Element) => {
  ReactDOM.render(component, container);

  return container;
};

const unmountReactElementRef = (container: Element) => {
  ReactDOM.unmountComponentAtNode(container);
};

export {mountReactElementRef, unmountReactElementRef};
