<?php
/**
 * Mockery
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://github.com/padraic/mockery/blob/master/LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to padraic@php.net so we can send you a copy immediately.
 *
 * @category   Mockery
 * @package    Mockery
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/mockery/blob/master/LICENSE New BSD License
 */

namespace Mockery\Generator\StringManipulation\Pass;

use Mockery\Generator\MockConfiguration;

class ClassNamePass implements Pass
{
    /**
     * @return string
     */
    public function apply($code, MockConfiguration $config)
    {
        $namespace = $config->getNamespaceName();

        $namespace = ltrim($namespace, "\\");

        $className = $config->getShortName();

        $code = str_replace(
            'namespace Mockery;',
            $namespace ? 'namespace ' . $namespace . ';' : '',
            $code
        );

        $code = str_replace(
            'class Mock',
            'class ' . $className,
            $code
        );

        return $code;
    }
}
