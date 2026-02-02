# PowerticSms - SMS Plugin for Mautic 7

Plugin for sending SMS via **HTTP Webhook** or **RabbitMQ/AMQP** with complete contact data.

## Features

- ✅ Send via HTTP Webhook (default)
- ✅ Send via RabbitMQ/AMQP (optional)
- ✅ Payload with complete contact data (standard fields, custom fields, tags, UTM)
- ✅ Configuration via Mautic plugins panel
- ✅ Dynamic toggle between HTTP and RabbitMQ in UI

## Requirements

- Mautic 7.0+
- PHP 8.2+
- (Optional) `php-amqplib/php-amqplib` ^3.0 for RabbitMQ support

## Installation

1. Copy the contents of this repository to `plugins/PowerticSmsBundle`

2. Clear the cache:

   ```bash
   php bin/console cache:clear
   ```

3. Install/update plugins:

   ```bash
   php bin/console mautic:plugins:reload
   ```

4. (Optional) To use RabbitMQ, install the dependency:

   ```bash
   composer require php-amqplib/php-amqplib:^3.0
   ```

## Configuration

1. Go to **Settings** → **Plugins** (`/s/plugins`)
2. Click on **Powertic SMS**
3. Enable the integration (Published: ON)
4. Choose the **Transport Type**:
   - **HTTP Webhook**: Configure URL and API Token
   - **RabbitMQ/AMQP**: Configure Host, Port, User, Password, Queue, etc.
5. Save the settings
6. Go to **Settings** → **Text Message Settings** (`/s/config/edit`)
7. Choose **Powertic SMS** as the default transport

## Payload Sent

Both via HTTP and RabbitMQ, the payload includes:

```json
{
  "to": "+5511999999999",
  "contents": [
    {
      "type": "text",
      "text": "SMS message content"
    }
  ],
  "contact": {
    "id": 123,
    "firstname": "John",
    "lastname": "Doe",
    "email": "john@example.com",
    "phone": "+5511999999999",
    "mobile": "+5511988888888",
    "company": "Company XYZ",
    "position": "Manager",
    "address1": "123 Example Street",
    "city": "New York",
    "state": "NY",
    "country": "United States",
    "points": 150,
    "tags": ["customer", "vip"],
    "utm": {
      "source": "google",
      "medium": "cpc",
      "campaign": "black-friday"
    },
    "date_added": "2025-01-15 10:30:00",
    "last_active": "2026-01-30 14:22:00",
    "custom_field": "value"
  },
  "timestamp": "2026-01-31T12:00:00.000000-03:00"
}
```

## HTTP Settings

| Field | Description |
|-------|-------------|
| Webhook URL | URL of the endpoint that will receive the POST |
| API Token | Token sent in the `X-API-TOKEN` header |

## RabbitMQ Settings

| Field | Description | Default |
|-------|-------------|---------|
| Host | RabbitMQ server | localhost |
| Port | AMQP port | 5672 |
| User | Username | guest |
| Password | Password | guest |
| Virtual Host | VHost | / |
| Queue | Queue name | sms_messages |
| Exchange | Exchange name (optional) | - |
| Routing Key | Routing key (optional) | - |

## Plugin Structure

```text
PowerticSmsBundle/
├── Assets/js/                    # JavaScript for UI
├── Config/
│   ├── config.php               # Bundle configuration
│   └── services.php             # Symfony services
├── Core/
│   ├── Configuration.php        # Configuration manager
│   ├── PowerticSmsClient.php    # HTTP client
│   ├── PowerticSmsTransport.php # Main transport
│   └── Transport/
│       ├── TransportInterface.php
│       ├── HttpTransport.php    # HTTP implementation
│       └── RabbitMqTransport.php # RabbitMQ implementation
├── EventSubscriber/
│   ├── AssetsSubscriber.php     # JS injection
│   └── MenuSubscriber.php       # SMS menu
├── Integration/
│   └── PowerticSmsIntegration.php # Integration and form
└── Translations/
    ├── en_US/messages.ini
    └── pt_BR/messages.ini
```

## Author

Luiz Eduardo Oliveira Fonseca <luiz@powertic.com>

## License

MIT
