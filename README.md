![Hoa](http://static.hoa-project.net/Image/Hoa_small.png)

Hoa is a **modular**, **extensible** and **structured** set of PHP libraries.
Moreover, Hoa aims at being a bridge between industrial and research worlds.

# Hoa\String

This library allows to manipulate UTF-8 strings easily with some other search
algorithms.

## Quick usage

We propose a quick overview of two usages: manipulate UTF-8 strings and one
search algorithm.

### Natural UTF-8 strings manipulation

The `Hoa\String` class allows to manipulate easily UTF-8 strings in a very
natural way. This class implements the `\ArrayAccess`, `\Countable` and
`\IteratorAggregate` interfaces. We will use the following examples:

    $french   = new Hoa\String('Je t\'aime');
    $arabic   = new Hoa\String('أحبك');
    $japanese = new Hoa\String('私はあなたを愛して');

To get the first character, we will do:

    var_dump(
        $french[0],  // string(1) "J"
        $arabic[0],  // string(2) "أ"
        $japanese[0] // string(3) "私"
    );

And to get the last character, we will do `[-1]`. It supports unbounded (and
modulo) indexes.

We note that it cares about text **direction**. Look at `$arabic[0]`, it returns
`أ` and not `ك`. To get the direction, we can use the `Hoa\String::getDirection`
method (which call the `Hoa\String::getCharDirection` static method), it returns
either `Hoa\String::LTR` (`0`) or `Hoa\String::RTL` (`1`):

    var_dump(
        $french->getDirection(),  // int(0)
        $arabic->getDirection(),  // int(1)
        $japanese->getDirection() // int(0)
    );

Text direction is also important for the `append`, `prepend`, `pad`… methods on
`Hoa\String` for example. 

To get the length of a string, we can use the `count` function:

    var_dump(
        count($french),  // int(9)
        count($arabic),  // int(4)
        count($japanese) // int(9)
    );

We are also able to iterate over the string:

    foreach($arabic as $letter)
        var_dump($letter);

    /**
     * Will output:
     *     string(2) "أ"
     *     string(2) "ح"
     *     string(2) "ب"
     *     string(2) "ك"
     */

Again, text direction is useful here. For `$arabic`, the iteration is done from
right to left.

Some static methods are helpful, such as `fromCode`, `toCode` or `isUtf8` on
`Hoa\String`:

    var_dump(
        Hoa\String::fromCode(0x1a9), // string(2) "Ʃ"
        Hoa\String::toCode('Ʃ'),     // int(425) == 0x1a9
        Hoa\String::isUtf8('Ʃ')      // bool(true)
    );

### Search algorithm

The `Hoa\String\Search` implements search algorithms on strings.

For example, the `Hoa\String\Search::approximated` method make a search by
approximated patterns (with *k* differences based upon the principle diagonal
monotony). If we search the word `GATAA` in `CAGATAAGAGAA` with 1 difference, we
will do:

    $search    = Hoa\String\Search::approximated(
        $haystack = 'CAGATAAGAGAA',
        $needle   = 'GATAA',
        $k        = 1
    );
    $solutions = array();

    foreach($search as $pos)
        $solutions[] = substr($haystack, $pos['i'], $pos['j'] - $pos['i']);

We will found `AGATA`, `GATAA`, `ATAAG` and `GAGAA`.

The result is not very handy but the algorithm is much optimized and found many
applications.

## Documentation

Different documentations can be found on the website:
[http://hoa-project.net/](http://hoa-project.net/).

## License

Hoa is under the New BSD License (BSD-3-Clause). Please, see
[`LICENSE`](http://hoa-project.net/LICENSE).