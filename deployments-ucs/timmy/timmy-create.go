package main

import (
    "context"
    "log"
    "os"
    "cloud.google.com/go/firestore"
)

func createClientFirestore(ctx context.Context, firestoreProjectID string) *firestore.Client {
    client, err := firestore.NewClient(ctx, firestoreProjectID)
    if err != nil {
            log.Fatalf("Failed to create client: %v", err)
    }

    return client
}

func setDocument(ctx context.Context, client *firestore.Client, collection string, document string, data map[string]interface{}) {
    result, err := client.Collection(collection).Doc(document).Set(ctx, data)
    if err != nil {
      log.Fatalln(err)
    }
    log.Print(result)
}

func deleteDocument(ctx context.Context, client *firestore.Client, collection string, document string) {
    result, err := client.Collection(collection).Doc(document).Delete(ctx)
    if err != nil {
      log.Fatalln(err)
    }
    log.Print(result)
}

func readDocument(ctx context.Context, client *firestore.Client, collection string, document string) map[string]interface{} {
    doc, err := client.Collection(collection).Doc(document).Get(ctx)
    if err != nil {
        log.Fatalf("Failed to iterate: %v", err)
    }
    return doc.Data()
}

func main() {
    ctx := context.Background()
    firestoreProjectID := "akecld-prd-pim-fire-eur-dev"

    // Firestore
    clientFirestore := createClientFirestore(ctx, firestoreProjectID)
    tenant_id := os.Args[1]
    tenant_name := os.Args[2]
    mysql_password := os.Args[3]
    email_password := os.Args[4]
    pim_secret := os.Args[5]
    collection := os.Args[6]
    pim_edition := os.Args[7]
    data := map[string]interface{}{
        "values": `{
            "AKENEO_PIM_URL": "https://` + tenant_name + `.pim-saas-dev.dev.cloud.akeneo.com",
            "APP_DATABASE_HOST": "pim-mysql.` + tenant_id + `.svc.cluster.local",
            "APP_INDEX_HOSTS": "elasticsearch-client.` + tenant_id + `.svc.cluster.local",
            "APP_TENANT_ID": "` + tenant_id + `",
            "MAILER_PASSWORD": "` + email_password + `",
            "MAILER_DSN": "smtp://` + tenant_name + `-akecld-prd-pim-saas-dev@mg.cloud.akeneo.com:` + email_password + `@smtp.mailgun.org:2525?encryption=tls&auth_mode=login",
            "MAILER_FROM": "Akeneo <no-reply-` + tenant_name + `.pim-saas-dev.dev.cloud.akeneo.com>",
            "MAILER_USER": "` + tenant_name + `-akecld-prd-pim-saas-dev@mg.cloud.akeneo.com",
            "MEMCACHED_SVC": "memcached.` + tenant_id + `.svc.cluster.local",
            "APP_DATABASE_PASSWORD": "` + mysql_password + `",
            "APP_SECRET": "` + pim_secret + `",
            "PFID": "` + tenant_id + `",
            "PIM_EDITION": "` + pim_edition + `",
            "SRNT_GOOGLE_BUCKET_NAME": "` + tenant_id + `"
          }`,
    }
    setDocument(ctx, clientFirestore, collection, tenant_id, data)
    defer clientFirestore.Close()
}
