# Howto

* build `docker build -t spaceflow .`
* run `docker run -p 8181:8181 --ulimit memlock=1024000 spaceflow:latest`

Dont forget to set `SLACK_WEBHOOK_URL` env variable, either through docker
or via dotenv file, otherwise the console command fails.

https://api.slack.com/messaging/webhooks#posting_with_webhooks

Visit http://localhost:8181/employees for API

There are five endpoints
* LIST at GET http://localhost:8181/employees
* DETAIL at GET http://localhost:8181/employees/{id}
* CREATE at POST http://localhost:8181/employees/{id}
* UPDATE at PUT http://localhost:8181/employees/{id}
* DELETE at DELETE http://localhost:8181/employees/{id}




Code coverage is at http://localhost:8181/code-coverage

### Few notes

The docker image should not be by any means considered what I would try 
to pass as production.

If, by any chance it fails to build during Fixtures, try decreasing
number of created entities.



