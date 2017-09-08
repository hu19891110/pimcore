<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Loader\Autoloader;

abstract class AbstractAutoloader
{
    /**
     * @param string $class
     *
     * @return bool
     */
    abstract public function load($class);

    /**
     * @param bool $prepend
     */
    public function register($prepend = false)
    {
        spl_autoload_register([$this, 'load'], true, $prepend);
    }

    public function unregister()
    {
        spl_autoload_unregister([$this, 'load']);
    }
}
