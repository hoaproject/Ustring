<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2013, Ivan Enderlin. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace {

from('Hoa')

/**
 * \Hoa\String\Exception
 */
-> import('String.Exception');

}

namespace Hoa\String {

/**
 * Class \Hoa\String.
 *
 * This class represents a UTF-8 string.
 * Please, see:
 *     • http://www.ietf.org/rfc/rfc3454.txt;
 *     • http://unicode.org/reports/tr9/;
 *     • http://www.unicode.org/Public/6.0.0/ucd/UnicodeData.txt.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2013 Ivan Enderlin.
 * @license    New BSD License
 */

class String implements \ArrayAccess, \Countable, \IteratorAggregate {

    /**
     * Left-To-Right.
     *
     * @const int
     */
    const LTR              = 0;

    /**
     * Right-To-Left.
     *
     * @const int
     */
    const RTL              = 1;

    /**
     * ZERO WIDTH NON-BREAKING SPACE (ZWNPBSP, aka byte-order mark, BOM).
     *
     * @const int
     */
    const BOM              = 0xfeff;

    /**
     * LEFT-TO-RIGHT MARK.
     *
     * @const int
     */
    const LRM              = 0x200e;

    /**
     * RIGHT-TO-LEFT MARK.
     *
     * @const int
     */
    const RLM              = 0x200f;

    /**
     * LEFT-TO-RIGHT EMBEDDING.
     *
     * @const int
     */
    const LRE              = 0x202a;

    /**
     * RIGHT-TO-LEFT EMBEDDING.
     *
     * @const int
     */
    const RLE              = 0x202b;

    /**
     * POP DIRECTIONAL FORMATTING.
     *
     * @const int
     */
    const PDF              = 0x202c;

    /**
     * LEFT-TO-RIGHT OVERRIDE.
     *
     * @const int
     */
    const LRO              = 0x202d;

    /**
     * RIGHT-TO-LEFT OVERRIDE.
     *
     * @const int
     */
    const RLO              = 0x202e;

    /**
     * Represent the beginning of the string.
     *
     * @const int
     */
    const BEGINNING        = 1;

    /**
     * Represent the end of the string.
     *
     * @const int
     */
    const END              = 2;

    /**
     * Split: non-empty pieces is returned.
     *
     * @const int
     */
    const WITHOUT_EMPTY    = PREG_SPLIT_NO_EMPTY;

    /**
     * Split: parenthesized expression in the delimiter pattern will be captured
     * and returned.
     *
     * @const int
     */
    const WITH_DELIMITERS  = PREG_SPLIT_DELIM_CAPTURE;

    /**
     * Split: offsets of captures will be returned.
     *
     * @const int
     */
    const WITH_OFFSET      = 260; //   PREG_OFFSET_CAPTURE
                                  // | PREG_SPLIT_OFFSET_CAPTURE

    /**
     * Group results by patterns.
     *
     * @const int
     */
    const GROUP_BY_PATTERN = PREG_PATTERN_ORDER;

    /**
     * Group results by tuple (set of patterns).
     *
     * @const int
     */
    const GROUP_BY_TUPLE   = PREG_SET_ORDER;

    /**
     * Current string.
     *
     * @var \Hoa\String string
     */
    protected $_string          = null;

    /**
     * Direction. Please see self::LTR and self::RTL constants.
     *
     * @var \Hoa\String int
     */
    protected $_direction       = null;

    /**
     * Collator.
     *
     * @var \Collator object
     */
    protected static $_collator = null;



    /**
     * Construct a UTF-8 string.
     *
     * @access  public
     * @param   string  $string    String.
     * @return  void
     */
    public function __construct ( $string = null ) {

        if(false === extension_loaded('mbstring'))
            throw new Exception(
                '%s needs the mbstring extension.', 0, get_class($this));

        if(null !== $string)
            $this->append($string);

        return;
    }

    /**
     * Append a substring to the current string, i.e. add to the end.
     *
     * @access  public
     * @param   string  $substring    Substring to append.
     * @return  \Hoa\String
     */
    public function append ( $substring ) {

        $direction = $this->getDirection();

        if(static::LTR === $this->getDirection())
            $this->_string .= $substring;
        else
            $this->_string  = $substring . $this->_string;

        $this->_direction  = null;

        return $this;
    }

    /**
     * Prepend a substring to the current string, i.e. add to the start.
     *
     * @access  public
     * @param   string  $substring    Substring to append.
     * @return  \Hoa\String
     */
    public function prepend ( $substring ) {

        $direction = $this->getDirection();

        if(static::LTR === $this->getDirection())
            $this->_string  = $substring . $this->_string;
        else
            $this->_string .= $substring;

        $this->_direction = null;

        return $this;
    }

    /**
     * Pad the current string to a certain length with another piece, aka piece.
     *
     * @access  public
     * @param   int     $length    Length.
     * @param   string  $piece     Piece.
     * @param   int     $side      Whether we append at the end or the beginning
     *                             of the current string.
     * @return  \Hoa\String
     */
    public function pad ( $length, $piece, $side = self::END ) {

        $difference = $length - $this->count();

        if(0 >= $difference)
            return $this;

        $handle = null;

        for($i = $difference / mb_strlen($piece) - 1; $i >= 0; --$i)
            $handle .= $piece;

        $handle .= mb_substr($piece, 0, $difference - mb_strlen($handle));

        return static::END === $side
                   ? $this->append($handle)
                   : $this->prepend($handle);
    }

    /**
     * Make a comparison with a string.
     * Return < 0 if current string is less than $string, > 0 if greater and 0
     * if equal.
     *
     * @access  public
     * @param   mixed  $string    String.
     * @return  int
     */
    public function compare ( $string ) {

        if(false === class_exists('Collator', false))
            return min(-1, max(1, strcmp($this->_string, (string) $string)));

        return static::getCollator()->compare($this->_string, $string);
    }

    /**
     * Get collator.
     *
     * @access  public
     * @return  \Collator
     */
    public static function getCollator ( ) {

        if(null === static::$_collator)
            static::$_collator = new \Collator(setlocale(LC_COLLATE, null));

        return static::$_collator;
    }

    /**
     * Ensure that the pattern is safe for Unicode: add the “u” option.
     *
     * @access  public
     * @param   string  $pattern    Pattern.
     * @return  string
     */
    public static function safePattern ( $pattern ) {

        $delimiter = mb_substr($pattern, 0, 1);
        $options   = mb_substr(
            mb_strrchr($pattern, $delimiter, false),
            mb_strlen($delimiter)
        );

        if(false === strpos($options, 'u'))
            $pattern .= 'u';

        return $pattern;
    }

    /**
     * Perform a regular expression (PCRE) match.
     *
     * @access  public
     * @param   string  $pattern    Pattern.
     * @param   array   $matches    Matches.
     * @param   int     $flags      Please, see constants self::WITH_OFFSET,
     *                              self::GROUP_BY_PATTERN and
     *                              self::GROUP_BY_TUPLE.
     * @param   int     $offset     Alternate place from which to start the
     *                              search.
     * @param   bool    $global     Whether the match is global or not.
     * @return  int
     */
    public function match ( $pattern, &$matches = null, $flags = 0,
                            $offset = 0, $global = false ) {

        $pattern = static::safePattern($pattern);

        if(true === $global && 0 === $flags)
            $flags = static::GROUP_BY_PATTERN;

        $offset = strlen(mb_substr($this->_string, 0, $offset));

        if(true === $global)
            return preg_match_all(
                $pattern,
                $this->_string,
                $matches,
                $flags,
                $offset
            );

        return preg_match($pattern, $this->_string, $matches, $flags, $offset);
    }

    /**
     * Perform a regular expression (PCRE) search and replace.
     *
     * @access  public
     * @param   mixed   $pattern        Pattern(s).
     * @param   mixed   $replacement    Replacement(s) (please, see
     *                                  preg_replace() documentation).
     * @param   int     $limit          Maximum of replacements. -1 for unbound.
     * @return  \Hoa\String
     */
    public function replace ( $pattern, $replacement, $limit = -1 ) {

        $pattern = static::safePattern($pattern);

        if(false === is_callable($replacement))
            $this->_string = preg_replace(
                $pattern,
                $replacement,
                $this->_string,
                $limit
            );
        else
            $this->_string = preg_replace_callback(
                $pattern,
                $replacement,
                $this->_string,
                $limit
            );

        return $this;
    }

    /**
     * Split the current string according to a given pattern (PCRE).
     *
     * @access  public
     * @param   string  $pattern    Pattern (as a regular expression).
     * @param   int     $limit      Maximum of split. -1 for unbound.
     * @param   int     $flags      Please, see constants self::WITHOUT_EMPTY,
     *                              self::WITH_DELIMITERS, self::WITH_OFFSET.
     * @return  array
     */
    public function split ( $pattern, $limit = -1,
                            $flags = self::WITHOUT_EMPTY ) {

        return preg_split(
            static::safePattern($pattern),
            $this->_string,
            $limit,
            $flags
        );
    }

    /**
     * Iterator over chars.
     *
     * @access  public
     * @return  \ArrayIterator
     */
    public function getIterator ( ) {

        return new \ArrayIterator(preg_split('#(?<!^)(?!$)#u', $this->_string));
    }

    /**
     * Perform a lowercase folding on the current string.
     *
     * @access  public
     * @return  \Hoa\String
     */
    public function toLowerCase ( ) {

        $this->_string = mb_strtolower($this->_string);

        return $this;
    }

    /**
     * Perform an uppercase folding on the current string.
     *
     * @access  public
     * @return  \Hoa\String
     */
    public function toUpperCase ( ) {

        $this->_string = mb_strtoupper($this->_string);

        return $this;
    }

    /**
     * Strip characters (default \s) of the current string.
     *
     * @access  public
     * @param   string  $regex    Characters to remove.
     * @param   int     $side     Whether we trim the beginning, the end or both
     *                            sides, of the current string.
     * @return  \Hoa\String
     */
    public function trim ( $regex = '\s', $side = 3 /*   static::BEGINNING
                                                       | static::END */ ) {

        $regex  = '(?:' . $regex . ')+';
        $handle = null;

        if(0 !== ($side & static::BEGINNING))
            $handle .= '(^' . $regex . ')';

        if(0 !== ($side & static::END)) {

            if(null !== $handle)
                $handle .= '|';

            $handle .= '(' . $regex . '$)';
        }

        $this->_string    = preg_replace('#' . $handle . '#u', '', $this->_string);
        $this->_direction = null;

        return $this;
    }

    /**
     * Compute offset (negative, unbound etc.).
     *
     * @access  protected
     * @param   int        $offset    Offset.
     * @return  int
     */
    protected function computeOffset ( $offset ) {

        $length = mb_strlen($this->_string);

        if(0 > $offset) {

            $offset = -$offset % $length;

            if(0 !== $offset)
                $offset = $length - $offset;
        }
        elseif($offset >= $length)
            $offset %= $length;

        return $offset;
    }

    /**
     * Get a specific chars of the current string.
     *
     * @access  public
     * @param   int     $offset    Offset (can be negative and unbound).
     * @return  string
     */
    public function offsetGet ( $offset ) {

        return mb_substr($this->_string, $this->computeOffset($offset), 1);
    }

    /**
     * Set a specific character of the current string.
     *
     * @access  public
     * @param   int     $offset    Offset (can be negative and unbound).
     * @param   string  $value     Value.
     * @return  \Hoa\String
     */
    public function offsetSet ( $offset, $value ) {

        $head   = null;
        $offset = $this->computeOffset($offset);

        if(0 < $offset)
            $head = mb_substr($this->_string, 0, $offset);

        $tail             = mb_substr($this->_string, $offset + 1);
        $this->_string    = $head . $value . $tail;
        $this->_direction = null;

        return $this;
    }

    /**
     * Delete a specific character of the current string.
     *
     * @access  public
     * @param   int     $offset    Offset (can be negative and unbound).
     * @return  string
     */
    public function offsetUnset ( $offset ) {

        return $this->offsetSet($offset, null);
    }

    /**
     * Check if a specific offset exists.
     *
     * @access  public
     * @return  bool
     */
    public function offsetExists ( $offset ) {

        return true;
    }

    /**
     * Reduce the strings.
     *
     * @access  public
     * @param   int  $start     Position of first character.
     * @param   int  $length    Maximum number of characters.
     * @return  \Hoa\String
     */
    public function reduce ( $start, $length = null ) {

        $this->_string = mb_substr($this->_string, $start, $length);

        return $this;
    }

    /**
     * Count number of characters of the current string.
     *
     * @access  public
     * @return  int
     */
    public function count ( ) {

        return mb_strlen($this->_string);
    }

    /**
     * Get byte (not character) at a specific offset.
     *
     * @access  public
     * @param   int     $offset    Offset (can be negative and unbound).
     * @return  string
     */
    public function getByteAt ( $offset ) {

        $length = strlen($this->_string);

        if(0 > $offset) {

            $offset = -$offset % $length;

            if(0 !== $offset)
                $offset = $length - $offset;
        }
        elseif($offset >= $length)
            $offset %= $length;

        return $this->_string[$offset];
    }

    /**
     * Count number of bytes (not characters) of the current string.
     *
     * @access  public
     * @return  int
     */
    public function getBytesLength ( ) {

        return strlen($this->_string);
    }

    /**
     * Get the width of the current string.
     * Useful when printing the string in monotype (some character need more
     * than one column to be printed).
     *
     * @access  public
     * @return  int
     */
    public function getWidth ( ) {

        return mb_strwidth($this->_string);
    }

    /**
     * Get direction of the current string.
     * Please, see the self::LTR and self::RTL constants.
     * It does not yet support embedding directions.
     *
     * @access  public
     * @return  int
     */
    public function getDirection ( ) {

        if(null === $this->_direction) {

            if(null === $this->_string)
                $this->_direction = static::LTR;
            else
                $this->_direction = static::getCharDirection(
                    mb_substr($this->_string, 0, 1)
                );
        }

        return $this->_direction;
    }

    /**
     * Get character of a specific character.
     * Please, see the self::LTR and self::RTL constants.
     *
     * @access  public
     * @param   string  $char    Character.
     * @return  int
     */
    public static function getCharDirection ( $char ) {

        $c = static::toCode($char);

        if(!(0x5be <= $c && 0x10b7f >= $c))
            return static::LTR;

        if(0x85e >= $c) {

            if(    0x5be === $c
               ||  0x5c0 === $c
               ||  0x5c3 === $c
               ||  0x5c6 === $c
               || (0x5d0 <= $c && 0x5ea >= $c)
               || (0x5f0 <= $c && 0x5f4 >= $c)
               ||  0x608 === $c
               ||  0x60b === $c
               ||  0x60d === $c
               ||  0x61b === $c
               || (0x61e <= $c && 0x64a >= $c)
               || (0x66d <= $c && 0x66f >= $c)
               || (0x671 <= $c && 0x6d5 >= $c)
               || (0x6e5 <= $c && 0x6e6 >= $c)
               || (0x6ee <= $c && 0x6ef >= $c)
               || (0x6fa <= $c && 0x70d >= $c)
               ||  0x710 === $c
               || (0x712 <= $c && 0x72f >= $c)
               || (0x74d <= $c && 0x7a5 >= $c)
               ||  0x7b1 === $c
               || (0x7c0 <= $c && 0x7ea >= $c)
               || (0x7f4 <= $c && 0x7f5 >= $c)
               ||  0x7fa === $c
               || (0x800 <= $c && 0x815 >= $c)
               ||  0x81a === $c
               ||  0x824 === $c
               ||  0x828 === $c
               || (0x830 <= $c && 0x83e >= $c)
               || (0x840 <= $c && 0x858 >= $c)
               ||  0x85e === $c)
                return static::RTL;
        }
        elseif(0x200f === $c)
            return static::RTL;
        elseif(0xfb1d <= $c) {

            if(    0xfb1d === $c
               || (0xfb1f <= $c && 0xfb28 >= $c)
               || (0xfb2a <= $c && 0xfb36 >= $c)
               || (0xfb38 <= $c && 0xfb3c >= $c)
               ||  0xfb3e === $c
               || (0xfb40 <= $c && 0xfb41 >= $c)
               || (0xfb43 <= $c && 0xfb44 >= $c)
               || (0xfb46 <= $c && 0xfbc1 >= $c)
               || (0xfbd3 <= $c && 0xfd3d >= $c)
               || (0xfd50 <= $c && 0xfd8f >= $c)
               || (0xfd92 <= $c && 0xfdc7 >= $c)
               || (0xfdf0 <= $c && 0xfdfc >= $c)
               || (0xfe70 <= $c && 0xfe74 >= $c)
               || (0xfe76 <= $c && 0xfefc >= $c)
               || (0x10800 <= $c && 0x10805 >= $c)
               ||  0x10808 === $c
               || (0x1080a <= $c && 0x10835 >= $c)
               || (0x10837 <= $c && 0x10838 >= $c)
               ||  0x1083c === $c
               || (0x1083f <= $c && 0x10855 >= $c)
               || (0x10857 <= $c && 0x1085f >= $c)
               || (0x10900 <= $c && 0x1091b >= $c)
               || (0x10920 <= $c && 0x10939 >= $c)
               ||  0x1093f === $c
               ||  0x10a00 === $c
               || (0x10a10 <= $c && 0x10a13 >= $c)
               || (0x10a15 <= $c && 0x10a17 >= $c)
               || (0x10a19 <= $c && 0x10a33 >= $c)
               || (0x10a40 <= $c && 0x10a47 >= $c)
               || (0x10a50 <= $c && 0x10a58 >= $c)
               || (0x10a60 <= $c && 0x10a7f >= $c)
               || (0x10b00 <= $c && 0x10b35 >= $c)
               || (0x10b40 <= $c && 0x10b55 >= $c)
               || (0x10b58 <= $c && 0x10b72 >= $c)
               || (0x10b78 <= $c && 0x10b7f >= $c))
                return static::RTL;
        }

        return static::LTR;
    }

    /**
     * Get a UTF-8 character from its decimal code representation.
     *
     * @access  public
     * @param   int  $code    Code.
     * @return  string
     */
    public static function fromCode ( $code ) {

        return mb_convert_encoding(
            '&#x' . dechex($code) . ';',
            'UTF-8',
            'HTML-ENTITIES'
        );
    }

    /**
     * Get a decimal code representation of a specific character.
     *
     * @access  public
     * @param   string  $char    Character.
     * @return  int
     */
    public static function toCode ( $char ) {

        $ucs = mb_convert_encoding($char, 'UCS-2LE', 'UTF-8');

        return ord($ucs[1]) * 256 + ord($ucs[0]);
    }

    /**
     * Get a binary representation of the decimal code of a specific character.
     *
     * @access  public
     * @param   string  $char      Character.
     * @param   int     $length    Length of the binary result.
     * @return  string
     */
    public static function toBinaryCode ( $char, $length = 32 ) {

        return vsprintf('%0' . intval($length) . 'b', static::toCode($char));
    }

    /**
     * Transcode.
     *
     * @access  public
     * @param   string  $string    String.
     * @param   string  $from      Original encoding.
     * @param   string  $to        Final encoding.
     * @return  string
     */
    public static function transcode ( $string, $from, $to = 'UTF-8' ) {

        return iconv($from, $to, $string);
    }

    /**
     * Check if a string is encoded in UTF-8.
     *
     * @access  public
     * @param   string  $string    String.
     * @return  bool
     */
    public static function isUtf8 ( $string ) {

        return (bool) preg_match('##u', $string);
    }

    /**
     * Transform a UTF-8 string into an ASCII one.
     *
     * @access  public
     * @param   bool  $try    Try something if \Normalizer is not present.
     * @return  \Hoa\String
     * @throw   \Hoa\String\Exception
     */
    public function toAscii ( $try = false ) {

        if(0 === preg_match('#[\x80-\xff]#', $this->_string))
            return $this;

        if(false === class_exists('Normalizer', false)) {

            if(false === $try)
                throw new Exception(
                    '%s needs the class Normalizer to work properly, ' .
                    'or you can force a try by using %1$s(true).',
                    1, array(__METHOD__));

            $string        = iconv('UTF-8', 'ASCII//IGNORE//TRANSLIT', $this->_string);
            $this->_string = preg_replace('#(?:[\'"`^](\w))#u', '\1', $string);

            return $this;
        }

        $string        = \Normalizer::normalize($this->_string, \Normalizer::NFKD);
        $string        = preg_replace('#\p{Mn}+#u', '', $string);
        $this->_string = iconv('UTF-8', 'ASCII//IGNORE//TRANSLIT', $string);

        return $this;
    }

    /**
     * Transform the object as a string.
     *
     * @access  public
     * @return  string
     */
    public function __toString ( ) {

        return $this->_string;
    }
}

}
