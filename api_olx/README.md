# OAuth2 SDK for PHP - Otodom API

## Introduction

This project aims to help developers use the Partner API (Otodom/OLX). The SDK provides comprehensive tools for managing advertisements through OAuth2 authorization.

## Technologies Used

- **PHP 7.4+** - programming language
- **Guzzle HTTP 7.3+** - HTTP client for API communication
- **OAuth2 Client** - library for OAuth2 authorization handling
- **PHP Data Structures (php-ds)** - data structures
- **Docker** - application containerization
- **PHPUnit 6.0+** - unit testing framework
- **Mockery** - mocking library for tests
- **PSR-4** - autoloading standard
- **Composer** - PHP dependency manager

## Requirements

- PHP 7.4 or newer
- Composer
- Docker (optional, for running local server)
- PHP extension: `ext-json`

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd demo_otodom-api
```

2. Install dependencies:
```bash
composer install
```

## Configuration

### Creating the Configuration File

It is mandatory to create a `config.json` file in the root directory of the project. The repository contains a `config.json.example` file that shows the configuration structure. Remove the `.example` extension from the file and change the values to your own.

Configuration parameters:

* `client_id` (required): OAuth2 client ID
* `client_secret` (required): OAuth2 client secret
* `api_key` (required): API key provided by OLX
* `base_url` (optional): Base URL for the OAuth2 server. Leave empty to use the default OLX server
* `oauth2_path` (optional): URL path pointing to OAuth2 resources. Leave empty to use the default value for OLX
* `user_agent` (optional): Your CRM name
* `code` (optional): When initializing the application, you must provide the code from the OAuth2 Authorization Code Flow

### Note: Don't Forget the Code

When running the project for the first time, no `Token` exists. For this reason, the configuration file must include a `code` field with the code from the OAuth2 Authorization Code Flow. Remember that this code is only valid for 60 seconds.

## Running

### Local Development Server

The project offers a Dockerfile that runs a local development server at `localhost:8080` to test the demo functionalities. File changes will automatically update the local server when the container is running.

#### Running the Server with Docker

```shell
docker-compose up
```

#### Running the Server with Docker Image Rebuild

```shell
docker-compose up --build
```

## Project Structure

```
demo_otodom-api/
├── src/                    # Source code
│   ├── Api/               # API classes
│   ├── Model/             # Data models
│   ├── Repository/        # Repositories
│   └── Service/           # Business services
├── examples/               # Usage examples
├── tests/                 # Unit tests
├── data/                  # Test data
└── config.json.example    # Example configuration
```

## Usage Examples

In the `/examples` folder there are files showing example usage of the tools available in this repository:

- `initialize.php` - service initialization
- `syncMetadata.php` - metadata synchronization
- `helpers.php` - helper functions

## Testing

Running unit tests:

```bash
composer test
```

or directly:

```bash
phpunit
```

## License

The project is available under the MIT license. Details can be found in the `LICENSE.md` file.
