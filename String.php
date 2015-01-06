<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2015, Ivan Enderlin. All rights reserved.
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

namespace Hoa\String;

use Hoa\Core;

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
 * @copyright  Copyright © 2007-2015 Ivan Enderlin.
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

        if(false === function_exists('mb_substr'))
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

        $this->_string .= $substring;

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

        $this->_string  = $substring . $this->_string;

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

        if(null === $collator = static::getCollator())
            return strcmp($this->_string, (string) $string);

        return $collator->compare($this->_string, $string);
    }

    /**
     * Get collator.
     *
     * @access  public
     * @return  \Collator
     */
    public static function getCollator ( ) {

        if(false === class_exists('Collator', false))
            return null;

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

        if(0 === $flags) {

            if(true === $global)
                $flags = static::GROUP_BY_PATTERN;
        }
        else
            $flags &= ~PREG_SPLIT_OFFSET_CAPTURE;


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
     * Get the number of column positions of a wide-character.
     *
     * This is a PHP implementation of wcwidth() and wcswidth() (defined in IEEE
     * Std 1002.1-2001) for Unicode, by Markus Kuhn. Please, see
     * http://www.cl.cam.ac.uk/~mgk25/ucs/wcwidth.c.
     *
     * The wcwidth(wc) function shall either return 0 (if wc is a null
     * wide-character code), or return the number of column positions to be
     * occupied by the wide-character code wc, or return -1 (if wc does not
     * correspond to a printable wide-character code).
     *
     * @access  public
     * @param   string  $char    Character.
     * @return  int
     */
    public static function getCharWidth ( $char ) {

        static $combining = [
            [0x0300,  0x036f],  [0x0483,  0x0486],  [0x0488,  0x0489],
            [0x0591,  0x05bd],  [0x05bf,  0x05bf],  [0x05c1,  0x05c2],
            [0x05c4,  0x05c5],  [0x05c7,  0x05c7],  [0x0600,  0x0603],
            [0x0610,  0x0615],  [0x064b,  0x065e],  [0x0670,  0x0670],
            [0x06d6,  0x06e4],  [0x06e7,  0x06e8],  [0x06ea,  0x06ed],
            [0x070f,  0x070f],  [0x0711,  0x0711],  [0x0730,  0x074a],
            [0x07a6,  0x07b0],  [0x07eb,  0x07f3],  [0x0901,  0x0902],
            [0x093c,  0x093c],  [0x0941,  0x0948],  [0x094d,  0x094d],
            [0x0951,  0x0954],  [0x0962,  0x0963],  [0x0981,  0x0981],
            [0x09bc,  0x09bc],  [0x09c1,  0x09c4],  [0x09cd,  0x09cd],
            [0x09e2,  0x09e3],  [0x0a01,  0x0a02],  [0x0a3c,  0x0a3c],
            [0x0a41,  0x0a42],  [0x0a47,  0x0a48],  [0x0a4b,  0x0a4d],
            [0x0a70,  0x0a71],  [0x0a81,  0x0a82],  [0x0abc,  0x0abc],
            [0x0ac1,  0x0ac5],  [0x0ac7,  0x0ac8],  [0x0acd,  0x0acd],
            [0x0ae2,  0x0ae3],  [0x0b01,  0x0b01],  [0x0b3c,  0x0b3c],
            [0x0b3f,  0x0b3f],  [0x0b41,  0x0b43],  [0x0b4d,  0x0b4d],
            [0x0b56,  0x0b56],  [0x0b82,  0x0b82],  [0x0bc0,  0x0bc0],
            [0x0bcd,  0x0bcd],  [0x0c3e,  0x0c40],  [0x0c46,  0x0c48],
            [0x0c4a,  0x0c4d],  [0x0c55,  0x0c56],  [0x0cbc,  0x0cbc],
            [0x0cbf,  0x0cbf],  [0x0cc6,  0x0cc6],  [0x0ccc,  0x0ccd],
            [0x0ce2,  0x0ce3],  [0x0d41,  0x0d43],  [0x0d4d,  0x0d4d],
            [0x0dca,  0x0dca],  [0x0dd2,  0x0dd4],  [0x0dd6,  0x0dd6],
            [0x0e31,  0x0e31],  [0x0e34,  0x0e3a],  [0x0e47,  0x0e4e],
            [0x0eb1,  0x0eb1],  [0x0eb4,  0x0eb9],  [0x0ebb,  0x0ebc],
            [0x0ec8,  0x0ecd],  [0x0f18,  0x0f19],  [0x0f35,  0x0f35],
            [0x0f37,  0x0f37],  [0x0f39,  0x0f39],  [0x0f71,  0x0f7e],
            [0x0f80,  0x0f84],  [0x0f86,  0x0f87],  [0x0f90,  0x0f97],
            [0x0f99,  0x0fbc],  [0x0fc6,  0x0fc6],  [0x102d,  0x1030],
            [0x1032,  0x1032],  [0x1036,  0x1037],  [0x1039,  0x1039],
            [0x1058,  0x1059],  [0x1160,  0x11ff],  [0x135f,  0x135f],
            [0x1712,  0x1714],  [0x1732,  0x1734],  [0x1752,  0x1753],
            [0x1772,  0x1773],  [0x17b4,  0x17b5],  [0x17b7,  0x17bd],
            [0x17c6,  0x17c6],  [0x17c9,  0x17d3],  [0x17dd,  0x17dd],
            [0x180b,  0x180d],  [0x18a9,  0x18a9],  [0x1920,  0x1922],
            [0x1927,  0x1928],  [0x1932,  0x1932],  [0x1939,  0x193b],
            [0x1a17,  0x1a18],  [0x1b00,  0x1b03],  [0x1b34,  0x1b34],
            [0x1b36,  0x1b3a],  [0x1b3c,  0x1b3c],  [0x1b42,  0x1b42],
            [0x1b6b,  0x1b73],  [0x1dc0,  0x1dca],  [0x1dfe,  0x1dff],
            [0x200b,  0x200f],  [0x202a,  0x202e],  [0x2060,  0x2063],
            [0x206a,  0x206f],  [0x20d0,  0x20ef],  [0x302a,  0x302f],
            [0x3099,  0x309a],  [0xa806,  0xa806],  [0xa80b,  0xa80b],
            [0xa825,  0xa826],  [0xfb1e,  0xfb1e],  [0xfe00,  0xfe0f],
            [0xfe20,  0xfe23],  [0xfeff,  0xfeff],  [0xfff9,  0xfffb],
            [0x10a01, 0x10a03], [0x10a05, 0x10a06], [0x10a0c, 0x10a0f],
            [0x10a38, 0x10a3a], [0x10a3f, 0x10a3f], [0x1d167, 0x1d169],
            [0x1d173, 0x1d182], [0x1d185, 0x1d18b], [0x1d1aa, 0x1d1ad],
            [0x1d242, 0x1d244], [0xe0001, 0xe0001], [0xe0020, 0xe007f],
            [0xe0100, 0xe01ef]
        ];

        $bisearch = function ( $c ) use ( $combining ) {

            $min = 0;
            $mid = 0;
            $max = count($combining) - 1;

            if($c < $combining[0][0] || $c > $combining[$max][1])
                return 0;

            while($max >= $min) {

                $mid = ($min + $max) / 2;

                if($c > $combining[$mid][1])
                    $min = $mid + 1;
                elseif($c < $combining[$mid][0])
                    $max = $mid - 1;
                else
                    return 1;
            }

            return 0;
        };

        $char = (string) $char;
        $c    = static::toCode($char);

        // Test for 8-bit control characters.
        if(0x0 === $c)
            return 0;

        if(0x20 > $c || (0x7f <= $c && $c < 0xa0))
            return -1;

        // Binary search in table of non-spacing characters.
        if($bisearch($c))
            return 0;

        // If we arrive here, $c is not a combining C0/C1 control character.

        return 1 +
            (0x1100 <= $c &&
                (0x115f >= $c ||                        // Hangul Jamo init. consonants
                 0x2329 === $c || 0x232a === $c ||
                     (0x2e80 <= $c && 0xa4cf >= $c &&
                      0x303f !== $c) ||                 // CJK…Yi
                     (0xac00  <= $c && 0xd7a3 >= $c) || // Hangul Syllables
                     (0xf900  <= $c && 0xfaff >= $c) || // CJK Compatibility Ideographs
                     (0xfe10  <= $c && 0xfe19 >= $c) || // Vertical forms
                     (0xfe30  <= $c && 0xfe6f >= $c) || // CJK Compatibility Forms
                     (0xff00  <= $c && 0xff60 >= $c) || // Fullwidth Forms
                     (0xffe0  <= $c && 0xffe6 >= $c) ||
                     (0x20000 <= $c && 0x2fffd >= $c) ||
                     (0x30000 <= $c && 0x3fffd >= $c)));
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

        $char = (string) $char;
        $code = ord($char[0]);

        if(!($code & 0x80)) // 0xxxxxxx
            return $code;

        if(($code & 0xe0) === 0xc0) { // 110xxxxx

            $bytes = 2;
            $code  = $code & ~0xc0;
        }
        elseif(($code & 0xf0) == 0xe0) { // 1110xxxx

            $bytes = 3;
            $code  = $code & ~0xe0;
        }

        elseif(($code & 0xf8) === 0xf0) { // 11110xxx

            $bytes = 4;
            $code  = $code & ~0xf0;
        }

        for($i = 2; $i <= $bytes; $i++) // 10xxxxxx
            $code = ($code << 6) + (ord($char[$i - 1]) & ~0x80);

        return $code;
    }

    /**
     * Get a binary representation of a specific character.
     *
     * @access  public
     * @param   string  $char    Character.
     * @return  string
     */
    public static function toBinaryCode ( $char ) {

        $char = (string) $char;
        $out  = null;

        for($i = 0, $max = strlen($char); $i < $max; ++$i)
            $out .= vsprintf('%08b', ord($char[$i]));

        return $out;
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
                    1, __METHOD__);

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
     * Copy current object string
     *
     * @return \Hoa\String
     */
    public function copy ( ) {

        return clone $this;
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

/**
 * Flex entity.
 */
Core\Consistency::flexEntity('Hoa\String\String');
