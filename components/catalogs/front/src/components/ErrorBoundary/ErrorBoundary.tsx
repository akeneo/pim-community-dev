import React, {ReactNode} from 'react';

type State = {
    hasError: boolean;
};

export class ErrorBoundary extends React.Component<unknown, State> {
    constructor(props: unknown) {
        super(props);
        this.state = {hasError: false};
    }

    static getDerivedStateFromError(): State {
        return {hasError: true};
    }

    render(): ReactNode {
        if (this.state.hasError) {
            // @todo
            return <h1>Something went wrong.</h1>;
        }

        return this.props.children;
    }
}
