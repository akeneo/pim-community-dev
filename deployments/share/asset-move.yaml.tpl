apiVersion: batch/v1
kind: Job
metadata:
  name: asset-move
spec:
  template:
    spec:
      containers:
      - name: asset-move
        image: google/cloud-sdk:alpine
        resources:
          requests:
            memory: "64Mi"
            cpu: "0.5"
          limits:
            memory: "128Mi"
            cpu: "0.7"
        command: ["/bin/sh", "-c"]
        args:
          - echo starting;
            cat /var/secrets/google/srnt.json | gcloud auth activate-service-account --key-file=-;
            gsutil -m cp -r ${ASSET_PATH}/* gs://${PFID}/asset_storage || true;
            echo asset done;
            gsutil -m cp -r ${CATALOG_PATH}/* gs://${PFID}/catalog_storage || true;
            echo catalog done;
            echo done;
        env:
          - name: PFID
            value: "${PFID}"
          - name: ASSET_PATH
            value: "/data/home/akeneo/pim/var/file_storage/asset"
          - name: CATALOG_PATH
            value: "/data/home/akeneo/pim/var/file_storage/catalog"
        volumeMounts:
          - name: flex-hd
            mountPath: /data
          - name: google-cloud-pim-storage-key
            mountPath: /var/secrets/google/srnt.json
            subPath: srnt.json
            readOnly: true
      restartPolicy: Never
      volumes:
      - name: flex-hd
        persistentVolumeClaim:
          claimName: pvc-flex-mover
      - name: google-cloud-pim-storage-key
        secret:
          secretName: pim-secrets
          items:
          - key: srntPimStorageKey
            path: srnt.json
  backoffLimit: 0
---
apiVersion: v1
kind: PersistentVolume
metadata:
  name: "pv-${NAMESPACE}-flex-mover"
spec:
  accessModes:
  - ReadWriteOnce
  capacity:
    storage: 1Gi
  gcePersistentDisk:
    pdName: ${FLEX_DISK_NAME}
  persistentVolumeReclaimPolicy: Retain
  storageClassName: ssd-retain
  claimRef:
    apiVersion: v1
    kind: PersistentVolumeClaim
    name: pvc-flex-mover
    namespace: "${NAMESPACE}"
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: pvc-flex-mover
spec:
  accessModes:
  - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
  storageClassName: ssd-retain
