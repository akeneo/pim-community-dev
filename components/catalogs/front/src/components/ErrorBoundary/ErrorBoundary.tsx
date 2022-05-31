import React, {ErrorInfo} from 'react';

export class ErrorBoundary extends React.Component<unknown, {hasError: boolean}> {
    constructor(props: unknown) {
        super(props);
        this.state = {hasError: false};
    }

    static getDerivedStateFromError(error: Error) {
        return {hasError: true};
    }

    componentDidCatch(error: Error, errorInfo: ErrorInfo) {
        // Vous pouvez aussi enregistrer l'erreur au sein d'un service de rapport.
        // logErrorToMyService(error, errorInfo);
    }

    render() {
        if (this.state.hasError) {
            return <h1>Something went wrong.</h1>;
        }

        return this.props.children;
    }
}
