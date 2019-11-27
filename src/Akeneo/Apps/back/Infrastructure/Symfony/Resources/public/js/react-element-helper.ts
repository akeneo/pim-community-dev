import ReactDOM from 'react-dom';
import {ReactElement} from 'react';

let container: Element | null = null;

export const mountReactElement = (component: ReactElement) => {
    if (null === container) {
        container = document.createElement('div');
        ReactDOM.render(component, container);
    }

    return container;
};

export const unmoundReactElement = () => {
    container && ReactDOM.unmountComponentAtNode(container);
    container = null;
};
