package main

import (
    "context"
    "log"
    "os"
    "cloud.google.com/go/firestore"
)

func createClientFirestore(ctx context.Context, projectID string) *firestore.Client {
    client, err := firestore.NewClient(ctx, projectID)
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
    projectID := "akecld-prd-pim-saas-dev"

    // Firestore
    clientFirestore := createClientFirestore(ctx, projectID)
    collection := "tenant_contexts"
    pfid := os.Args[1]
    deleteDocument(ctx, clientFirestore, collection, pfid)
    defer clientFirestore.Close()
}
