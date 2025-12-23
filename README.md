# Simplified Fintech API

This is a simplified fintech application designed to simulate transactions between client and shop wallets.

The registration methods exist primarily for data population purposes. Additionally, certain wallet and transfer GET methods are provided solely for demonstration; therefore, these endpoints are not authenticated and do not include security layers.

The core feature of this application is the `POST /api/transfer` endpoint. It manages transfers between users (Common to Common or Common to Shop) while ensuring atomicity by implementing database row-level locking on the user's wallet and dispatching an event to process notifications via background jobs.

### External Services and Reliability

Transfers integrate with external authorization and notification services. The system was originally designed to consume:

* Authorization: `https://util.devi.tools/api/v2/authorize`
* Notification: `https://util.devi.tools/api/v1/notify`

Due to the current instability of these providers, mock implementations were developed to simulate network delays and service failures, ensuring the application remains testable and resilient.

## Transfer Flow

The following diagram describes the business logic orchestrated by the `HandleTransferAction`:

```mermaid
graph LR
    Start((Start Transfer)) --> IsShop{Payer is a Shop?}

    IsShop -- Yes --> 403[403 Forbidden]
    IsShop -- No --> Bal1{First Balance Check}

    Bal1 -- Insufficient --> 422a[422 Unprocessable Entity]
    Bal1 -- Sufficient --> Auth{External Authorization}

    Auth -- Denied --> 401[401 Unauthorized]
    Auth -- Approved --> Lock[Lock Wallet Row<br/>SELECT FOR UPDATE]

    Lock --> Bal2{Second Balance Check}

    Bal2 -- Insufficient --> Rollback[Rollback & 422 Error]
    Bal2 -- Sufficient --> Update[Update Balances &<br/>Create Record]

    Update --> Commit[Commit Transaction]
    Commit --> Notify["Dispatch TransferCompletedEvent
    (Notifications Job)"]
    Notify --> Success((201 Success))

    style 403 fill:#ff2c2c,color:#fff
    style 422a fill:#ff2c2c,color:#fff
    style 401 fill:#ff2c2c,color:#fff
    style Rollback fill:#ff2c2c,color:#fff
    style Success fill:#008000,color:#fff

```

## API Documentation

The complete API documentation, generated via Scribe, is available at:
`/docs`
