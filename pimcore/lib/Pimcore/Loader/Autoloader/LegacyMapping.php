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

class LegacyMapping extends AbstractAutoloader
{
    /**
     * Mapping from old to new class name
     *
     * @var array
     */
    private $mapping = [
        'Pimcore\Glossary\Processor' => \Pimcore\Tool\Glossary\Processor::class
    ];

    public function load($class)
    {
        if (!isset($this->mapping[$class])) {
            return false;
        }

        @trigger_error(
            sprintf(
                '"%s" is deprecated. Please use "%s" instead.',
                $class, $this->mapping[$class]
            ),
            E_USER_DEPRECATED
        );

        class_alias($this->mapping[$class], $class);

        return true;
    }
}
