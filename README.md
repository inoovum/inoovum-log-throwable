# inoovum® throwable log

This package extends the throwable log.

### Installation

Just run:
`composer require inoovum/log-throwable`

### Configuration
You can define your own PHP classes. The Neos Flow exception messages will be passed to them. For example, to a specific slack channel.

```yaml
Inoovum:
  Log:
    Throwable:
      options:
        writeToFile: false # Disable writing log files to local storage
      classes:
        -
          class: 'Inoovum\Log\Throwable\Log\SlackMessage'
          options:
            webhookUri: 'https://hooks.slack.com/services/T1TCCUN3X/A17PWTYX3GZ/xs83nHlpZafgUieYzsKiUcfa'
```

## Author

* E-Mail: patric.eckhart@steinbauer-it.com
* URL: http://www.steinbauer-it.com
