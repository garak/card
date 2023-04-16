# Contributing

First of all, **thank you** for contributing, **you are awesome**!

Here are a few rules to follow in order to ease code reviews, and discussions before
maintainers accept and merge your work.

You MUST follow our coding standards.

You MUST run the test suite.

You MUST write (or update) unit tests.

You SHOULD write documentation.

Please, write [commit messages that make sense][1], and [rebase your branch][2]
before submitting your Pull Request.

Thank you!

## Setup your local environment

You need [docker with docker-compose][3]. Optional use of [make][4] is suggested,
but not mandatory (you can take a look to the Makefile and run direct your commands).

First, execute `make build`. After that, you can execute `make start` and then `make install`.

## Running the test suite

Execute `make test`.

## Ensuring code quality and standards

Execute `make cs` for code quality and `make stan` for static analysis.

[1]: https://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html
[2]: https://git-scm.com/book/en/v2/Git-Branching-Rebasing
[3]: https://docs.docker.com/compose/
[4]: https://opensource.com/article/18/8/what-how-makefile
