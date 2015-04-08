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

namespace Hoa\Compiler\Test\Unit\Llk;

use Hoa\Test;
use Hoa\Compiler as LUT;
use Hoa\File;

/**
 * Class \Hoa\Compiler\Test\Unit\Documentation.
 *
 * Test suite of the examples in the documentation.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2015 Ivan Enderlin.
 * @license    New BSD License
 */

class Llk extends Test\Unit\Suite {

    public function case_without_unification ( ) {

        $_grammar = <<<GRAMMAR
%token  quote   '|"
%token  string  \w+

rule:
    ::quote:: <string> ::quote::
GRAMMAR;

        $this
            ->given(
                $grammar = new File\ReadWrite('hoa://Test/Vfs/WithoutUnification.pp'),
                $grammar->writeAll($_grammar),
                $compiler = LUT\Llk::load($grammar)
            )
            ->when($result = $compiler->parse('"foo"', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = $compiler->parse('\'foo"', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = $compiler->parse('"foo\'', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = $compiler->parse('\'foo\'', null, false))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_unification ( ) {

        $_grammar = <<<GRAMMAR
%token  quote   '|"
%token  string  \w+

rule:
    ::quote[0]:: <string> ::quote[0]::
GRAMMAR;

        $this
            ->given(
                $grammar = new File\ReadWrite('hoa://Test/Vfs/Unification.pp'),
                $grammar->writeAll($_grammar),
                $compiler = LUT\Llk::load($grammar)
            )
            ->when($result = $compiler->parse('"foo"', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = $compiler->parse('\'foo\'', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->exception(function ( ) use ( &$compiler ) {

                $compiler->parse('\'foo"', null, false);
            })
                ->isInstanceOf('Hoa\Compiler\Exception\UnexpectedToken')

            ->exception(function ( ) use ( &$compiler ) {

                $compiler->parse('"foo\'', null, false);
            })
                ->isInstanceOf('Hoa\Compiler\Exception\UnexpectedToken');
    }

    public function case_unification_palindrome ( ) {

        $_grammar = <<<GRAMMAR
%token t \w

root:
    ::t[0]:: root()? ::t[0]::
GRAMMAR;

        $this
            ->given(
                $grammar = new File\ReadWrite('hoa://Test/Vfs/Palindrome.pp'),
                $grammar->writeAll($_grammar),
                $compiler = LUT\Llk::load($grammar)
            )
            ->when($result = $compiler->parse('aa', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = $compiler->parse('abba', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = $compiler->parse('abccba', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->when($result = $compiler->parse('abcddcba', null, false))
            ->then
                ->boolean($result)
                    ->isTrue()

            ->exception(function ( ) use ( &$compiler ) {

                $compiler->parse('abcdcba', null, false);
            })
                ->isInstanceOf('Hoa\Compiler\Exception\UnexpectedToken');
    }
}
