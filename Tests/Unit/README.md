# Running Unit Tests for the Google Indexer Extension

### To run unit tests for this TYPO3 extension, follow these steps:

## 1. Install Dependencies
Ensure that you have the necessary dependencies installed, including PHPUnit and vfsStream. To do this, navigate to the `google-indexer` folder in your console and run:

```bash
composer update
```

## 2. Run Unit Tests
Once the dependencies are installed, you can execute the unit tests by running the following command:

```bash
vendor/bin/phpunit vendor/drblitz/google-indexer/Tests
```

PHPUnit will then run the tests and display the results in the terminal, indicating which tests passed, failed, or were skipped.