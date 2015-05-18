![Hoa](http://static.hoa-project.net/Image/Hoa_small.png)

Hoa is a **modular**, **extensible** and **structured** set of PHP libraries.
Moreover, Hoa aims at being a bridge between industrial and research worlds.

# Hoa\String ![state](http://central.hoa-project.net/State/String)

This library allows to manipulate UTF-8 strings easily with some search
algorithms.

## Installation

With [Composer](http://getcomposer.org/), to include this library into your
dependencies, you need to require
[`hoa/string`](https://packagist.org/packages/hoa/string):

```json
{
    "require": {
        "hoa/string": "~2.0"
    }
}
```

Please, read the website to [get more informations about how to
install](http://hoa-project.net/Source.html).

## Quick usage

We propose a quick overview of two usages: manipulate UTF-8 strings and one
search algorithm.

### Natural UTF-8 strings manipulation

The `Hoa\String\String` class allows to manipulate easily UTF-8 strings in a
very natural way. This class implements the `\ArrayAccess`, `\Countable` and
`\IteratorAggregate` interfaces. We will use the following examples:

```php
$french   = new Hoa\String\String('Je t\'aime');
$arabic   = new Hoa\String\String('أحبك');
$japanese = new Hoa\String\String('私はあなたを愛して');
```

To get the first character, we will do:

```php
var_dump(
    $french[0],  // string(1) "J"
    $arabic[0],  // string(2) "أ"
    $japanese[0] // string(3) "私"
);
```

And to get the last character, we will do `[-1]`. It supports unbounded (and
modulo) indexes.

We note that it cares about text **direction**. Look at `$arabic[0]`, it returns
`أ` and not `ك`. To get the direction, we can use the
`Hoa\String\String::getDirection` method (which call the
`Hoa\String\String::getCharDirection` static method), it returns either
`Hoa\String\String::LTR` (`0`) or `Hoa\String\String::RTL` (`1`):

```php
var_dump(
    $french->getDirection(),  // int(0)
    $arabic->getDirection(),  // int(1)
    $japanese->getDirection() // int(0)
);
```

Text direction is also important for the `append`, `prepend`, `pad`… methods on
`Hoa\String\String` for example. 

To get the length of a string, we can use the `count` function:

```php
var_dump(
    count($french),  // int(9)
    count($arabic),  // int(4)
    count($japanese) // int(9)
);
```

We are also able to iterate over the string:

```php
foreach ($arabic as $letter) {
    var_dump($letter);
}

/**
 * Will output:
 *     string(2) "أ"
 *     string(2) "ح"
 *     string(2) "ب"
 *     string(2) "ك"
 */
```

Again, text direction is useful here. For `$arabic`, the iteration is done from
right to left.

Some static methods are helpful, such as `fromCode`, `toCode` or `isUtf8` on
`Hoa\String\String`:

```php
var_dump(
    Hoa\String\String::fromCode(0x1a9), // string(2) "Ʃ"
    Hoa\String\String::toCode('Ʃ'),     // int(425) == 0x1a9
    Hoa\String\String::isUtf8('Ʃ')      // bool(true)
);
```

We can also transform any text into ASCII:

```php
$emoji = new Hoa\String\String('I ❤ Unicode');
$maths = new Hoa\String\String('∀ i ∈ ℕ');

echo
    $emoji->toAscii(), "\n",
    $maths->toAscii(), "\n";

/**
 * Will output:
 *     I (heavy black heart) Unicode
 *     (for all) i (element of) N
 */
```

### Search algorithm

The `Hoa\String\Search` implements search algorithms on strings.

For example, the `Hoa\String\Search::approximated` method make a search by
approximated patterns (with *k* differences based upon the principle diagonal
monotony). If we search the word `GATAA` in `CAGATAAGAGAA` with 1 difference, we
will do:

```php
$search = Hoa\String\Search::approximated(
    $haystack = 'CAGATAAGAGAA',
    $needle   = 'GATAA',
    $k        = 1
);
$solutions = array();

foreach ($search as $pos) {
    $solutions[] = substr($haystack, $pos['i'], $pos['l']);
}
```

We will found `AGATA`, `GATAA`, `ATAAG` and `GAGAA`.

The result is not very handy but the algorithm is much optimized and found many
applications.

## Documentation

Different documentations can be found on the website:
[http://hoa-project.net/](http://hoa-project.net/).

## License

Hoa is under the New BSD License (BSD-3-Clause). Please, see
[`LICENSE`](http://hoa-project.net/LICENSE).
