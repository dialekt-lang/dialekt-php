# Dialekt

[![Build Status]](https://travis-ci.org/IcecaveStudios/dialekt)
[![Test Coverage]](https://coveralls.io/r/IcecaveStudios/dialekt?branch=develop)
[![SemVer]](http://semver.org)

**Dialekt** is a very simple language for representing boolean expressions of the form often used by search engines.

* Install via [Composer](http://getcomposer.org) package [icecave/dialekt](https://packagist.org/packages/icecave/dialekt)
* Read the [API documentation](http://icecavestudios.github.io/dialekt/artifacts/documentation/api/)
* Try the [online demo](http://dialekt.icecave.com.au)

## Rationale and Concepts

**Dialekt** was devised for specifing filters for lists of objects based on sets of human-readable "tags" or "labels".
This PHP package provides an [expression parser](src/Parser/ExpressionParser.php) which parses **Dialekt** expressions
into an [abstract syntax tree](http://en.wikipedia.org/wiki/Abstract_syntax_tree), a [list parser](src/Parser/ListParser.php)
which parses lists of tags into arrays, and an [evaluator](src/Evaluator/Evaluator.php) which checks if a given set of
tags matches a particular expression.

Additionally, the nodes of the abstract syntax tree implement the [visitor pattern](http://en.wikipedia.org/wiki/Visitor_pattern),
allowing the creation of new algorithms that traverse the syntax tree. This could be used, for example, to generate an
SQL query that finds entities with tags that match the expression.

## Syntax

Below is an example of a **Dialekt** expression showing the available language features. You can see how this expression
is parsed using the [online demo](http://dialekt.icecave.com.au/?expr=foo+bar+AND+%28baz+OR+qux%29+AND+NOT+%22doom+hammer%22).

```vb
foo bar AND (baz OR qux) AND NOT "doom hammer"
```

In the above example, `foo`, `bar`, `baz`, `qux` and `"doom hammer"` are examples of *tags*. The tag is the most
basic syntactic element of an expression. An expression is composed of one or more tags, and the logical operators `AND`,
`OR` and `NOT`. The logical operators are case-insensitive.

Tags may be formed by any non-whitespace characters except the parentheses and asterisk characters. Tags may optionally
be enclosed in double-quotes, as the `doom hammer` tag has been in the example above. Doing so allows the inclusion
of parentheses and whitespace characters. Tags must also be enclosed in double-quotes if they are one of the reserved
words `AND`, `OR` or `NOT`. Inside a quoted tag the backslash character provides escaping functionality similar to a
double-quoted PHP string.

## Implicit AND operator

When one tag is directly adjacent to another (as `foo` and `bar` are above) they are treated equivalent to an `AND`,
thus the expression `foo bar baz` is equivalent to `foo AND bar AND baz`.

## Grouping and operator precedence

Sub-expressions can be grouped using parenthesis to control evaluation order, as `baz` and `qux` have been in the
example above.

When parenthesis are not present, the `AND` operator has higher precedence than `OR` such that `foo OR bar AND baz` is
equivalent to `foo OR (bar AND baz)`.

<!-- references -->
[Build Status]: http://img.shields.io/travis/IcecaveStudios/dialekt/develop.svg?style=flat-square
[Test Coverage]: http://img.shields.io/coveralls/IcecaveStudios/dialekt/develop.svg?style=flat-square
[SemVer]: http://img.shields.io/:semver-0.1.0-yellow.svg?style=flat-square
