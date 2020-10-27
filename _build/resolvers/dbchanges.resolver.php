<?php
/**
 * Quip
 *
 * Copyright 2010-11 by Shaun McCormick <shaun@modx.com>
 *
 * This file is part of Quip, a simple commenting component for MODx Revolution.
 *
 * Quip is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Quip is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Quip; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package quip
 */
/**
 * Resolves db changes
 *
 * @var xPDOObject $object
 * @var array $options
 *
 * @package quip
 * @subpackage build
 */
if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx =& $object->xpdo;
            $modelPath = $modx->getOption('quip.core_path',null,$modx->getOption('core_path').'components/quip/').'model/';
            $modx->addPackage('quip',$modelPath);

            $manager = $modx->getManager();

            $class = 'quipThread';
            if ($manager->removeIndex($class, 'moderated')) {
                $manager->addIndex($class,'moderated');
            }
            if ($manager->removeIndex($class, 'moderator_group')) {
                $manager->addIndex($class,'moderator_group');
            }
            if ($manager->removeIndex($class, 'resource')) {
                $manager->addIndex($class,'resource');
            }

            $class = 'quipComment';
            if ($manager->removeIndex($class, 'thread')) {
                $manager->addIndex($class,'thread');
            }
            if ($manager->removeIndex($class, 'parent')) {
                $manager->addIndex($class,'parent');
            }
            if ($manager->removeIndex($class, 'author')) {
                $manager->addIndex($class,'author');
            }
            if ($manager->removeIndex($class, 'approved')) {
                $manager->addIndex($class,'approved');
            }
            if ($manager->removeIndex($class, 'approvedby')) {
                $manager->addIndex($class,'approvedby');
            }
            if ($manager->removeIndex($class, 'deleted')) {
                $manager->addIndex($class,'deleted');
            }
            if ($manager->removeIndex($class, 'deletedby')) {
                $manager->addIndex($class,'deletedby');
            }
            if ($manager->removeIndex($class, 'resource')) {
                $manager->addIndex($class,'resource');
            }

            $class = 'quipCommentNotify';
            if ($manager->removeIndex($class, 'thread')) {
                $manager->addIndex($class,'thread');
            }
            if ($manager->removeIndex($class, 'user')) {
                $manager->addIndex($class,'user');
            }

            break;
    }
}
return true;