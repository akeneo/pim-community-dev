# How to use argoCD

In order to access the server UI you have the following options:
```
1. Get admin credential
kubectl -n argocd get secret argocd-initial-admin-secret -o jsonpath="{.data.password}" | base64 -d && echo
2. Activate port forwarding (not needed anymore)
kubectl port-forward service/argocd-server -n argocd 8080:443
```
Add private repo https://argo-cd.readthedocs.io/en/release-1.8/user-guide/private-repositories/
Add new synchro https://argo-cd.readthedocs.io/en/stable/getting_started/
