apiVersion: batch/v1
kind: Job
metadata:
  name: es-data-move
spec:
  template:
    spec:
      containers:
      - name: es-data-move
        image: alpine:3
        resources:
          requests:
            memory: "64Mi"
            cpu: "0.5"
          limits:
            memory: "128Mi"
            cpu: "0.7"
        command: ["/bin/sh", "-c"]
        args:
          - rm -rf /data-es/*;
            cp -r /data/var/lib/elasticsearch/* /data-es;
            chown -R 1000:1000 /data-es;
            echo done;
        env:
          - name: SOURCE_PFID
            value: "${SOURCE_PFID}"
        volumeMounts:
          - name: flex-hd
            mountPath: /data
          - name: es-hd
            mountPath: /data-es
      restartPolicy: Never
      volumes:
      - name: flex-hd
        persistentVolumeClaim:
          claimName: pvc-flex-mover
      - name: es-hd
        persistentVolumeClaim:
          claimName: pvc-es-mover
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
kind: PersistentVolume
metadata:
  name: "pv-${NAMESPACE}-es-mover"
spec:
  accessModes:
  - ReadWriteOnce
  capacity:
    storage: 1Gi
  gcePersistentDisk:
    pdName: ${ES_DISK_NAME}
  persistentVolumeReclaimPolicy: Retain
  storageClassName: ssd-retain-csi
  claimRef:
    apiVersion: v1
    kind: PersistentVolumeClaim
    name: pvc-es-mover
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
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: pvc-es-mover
spec:
  accessModes:
  - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
  storageClassName: ssd-retain-csi
