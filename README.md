# Dialekt

[![Build Status]](https://travis-ci.org/IcecaveStudios/dialekt)
[![Test Coverage]](https://coveralls.io/r/IcecaveStudios/dialekt?branch=develop)
[![SemVer]](http://semver.org)

**Dialekt** is a very simple language designed to represent boolean expressions such as might be used in a search
implementation. This package provides a parser and syntax-tree representation that can be used as an intermediate step
in converting text input into an appropriate format, such as an SQL query.

* Install via [Composer](http://getcomposer.org) package [icecave/dialekt](https://packagist.org/packages/icecave/dialekt)
* Read the [API documentation](http://icecavestudios.github.io/dialekt/artifacts/documentation/api/)
* Try the [online demo](http://dialekt.icecave.com.au)

## Syntax

### Tags

The most basic element of a **Dialekt** expression is the *tag*. A tag is formed by any non whitespace characters,
excluding parentheses and the three reserved words `AND`, `OR` and `NOT`.

Example:
```vb
foo
```

When enclosed in double-quotes tags may include whitespace, or represent the English words "and", "or" and "not".

Examples:
```vb
"foo bar"
"and"
```

### Patterns

```
TODO
```

### Operators

```
TODO
```

### Precedence

```
TODO
```

<!-- references -->
[Build Status]: http://img.shields.io/travis/IcecaveStudios/dialekt/develop.svg
[Test Coverage]: http://img.shields.io/coveralls/IcecaveStudios/dialekt/develop.svg
[SemVer]: http://img.shields.io/:semver-0.0.0-red.svg
