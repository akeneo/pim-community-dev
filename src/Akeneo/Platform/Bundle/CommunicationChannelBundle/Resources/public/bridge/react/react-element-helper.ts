import ReactDOM from 'react-dom';

const mountReactElementRef = (component: JSX.Element, container: Element) => {
  ReactDOM.render(component, container);

  return container;
};

export {mountReactElementRef};
