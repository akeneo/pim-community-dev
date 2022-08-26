# wiremock-helm
Helm Chart for deployment Wiremock to Kubernetes

# Quick Start
## Pre-requisites
1. [Install minikube](https://kubernetes.io/docs/tasks/tools/install-minikube/)
2. [Install helm](https://helm.sh/docs/intro/install/)
3. Deploy Wiremock
    ```bash
    helm upgrade --install wiremock ./charts/wiremock
    ```
4. Verify Wiremock deployment
    ```bash
    $ export POD_NAME=$(kubectl get pods --namespace {{ .Release.Namespace }} -l "app.kubernetes.io/name={{ include "wiremock.name" . }},app.kubernetes.io/instance={{ .Release.Name }}" -o jsonpath="{.items[0].metadata.name}")

    $ kubectl port-forward $POD_NAME 8080:{{ .Values.service.internalPort}}
    ```
    Visit http://127.0.0.1:8080/__admin/webapp on your browser.
5. Verifying a response using Wiremock, run
    ```
    $ curl -X POST http://127.0.0.1:8080/v1/hello
    ```

# References:
* https://github.com/holomekc/wiremock
* https://github.com/tomakehurst/wiremock