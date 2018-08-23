<?php
/**
 * @category    Ruchlewicz
 * @package     Ruchlewicz_ConsoleCommand
 * @author      Sebastian Ruchlewicz <sebastian.ruchlewicz@gmail.com>
 * @copyright   Copyright (c) Sebastian Ruchlewicz (https://ruchlewicz.net/)
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Ruchlewicz_ConsoleCommand',
    __DIR__
);