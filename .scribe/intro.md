# Introduction



<aside>
    <strong>Base URL</strong>: <code>http://127.0.0.1</code>
</aside>

    This is the documentation for the simplified fintech application. It aims to simulate transactions between clients and shop wallets.

    The registration methods exist only for data population purposes, and certain wallet and transfer GET methods are provided solely for demonstration. This is why these endpoints are not authenticated and do not include security layers.

    The main endpoint of this application is POST /api/transfer. It handles transfers between users (common to common or common to shop) while ensuring atomicity by locking the user's wallet in the database and sending notifications via background jobs.

