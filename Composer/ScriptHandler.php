<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\Composer;
use Composer\Script\Event;

use Symfony\Component\Process\Process;

/**
 * A hack to work around the missing support for js assets in composer
 *
 * @see http://groups.google.com/group/composer-dev/browse_thread/thread/e9e2f7d919aadfec
 *
 * @author David Buchmann
 */
class ScriptHandler
{
    const CREATE_COMMIT_ID = 'a148ce9633535930d7b4b70cc1088102f5c5eb90';

    const CKEDITOR_COMMIT_ID = '0fb9d534634a06af386027bd7dea2c9dcfb8bb99';

    public static function downloadCreateAndCkeditor(Event $event)
    {
        ScriptHandler::downloadCreate($event);
        ScriptHandler::downloadCkeditor($event);
    }

    public static function downloadCreate(Event $event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $event->getIO()->write("<info>Download or update create</info>");

        // directory where the repository should be clone into
        if (isset($extra['create-directory'])) {
            $directory = getcwd() . '/' . $extra['create-directory'];
        } else {
            $directory = __DIR__ . '/../Resources/public/vendor/create';
        }

        // git repository
        if (isset($extra['create-repository'])) {
            $repository = $extra['create-repository'];
        } else {
            $repository = 'https://github.com/bergie/create.git';
        }

        // commit id
        if (isset($extra['create-commit'])) {
            $commit = $extra['create-commit'];
        } else {
            $commit = ScriptHandler::CREATE_COMMIT_ID;
        }

        ScriptHandler::gitSynchronize($directory, $repository, $commit);
    }

    public static function downloadCkeditor(Event $event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $event->getIO()->write("<info>Download or update ckeditor</info>");

        // directory where the repository should be clone into
        if (isset($extra['ckeditor-directory'])) {
            $directory = getcwd() . '/' . $extra['ckeditor-directory'];
        } else {
            $directory = __DIR__ . '/../Resources/public/vendor/ckeditor';
        }

        // git repository
        if (isset($extra['ckeditor-repository'])) {
            $repository = $extra['ckeditor-repository'];
        } else {
            $repository = 'https://github.com/ckeditor/ckeditor-releases.git';
        }

        // commit id
        if (isset($extra['ckeditor-commit'])) {
            $commit = $extra['ckeditor-commit'];
        } else {
            $commit = ScriptHandler::CKEDITOR_COMMIT_ID;
        }

        ScriptHandler::gitSynchronize($directory, $repository, $commit);
    }

    /**
     * @throws \RuntimeException
     * @param  string            $directory  The directory where the repository should be clone into
     * @param  string            $repository The git repository
     * @param  string            $commitId   The commit id
     */
    public static function gitSynchronize($directory, $repository, $commitId)
    {
        $currentDirectory = getcwd();
        $parentDirectory = dirname($directory);
        $projectDirectory = basename($directory);

        $status = null;
        $output = array();
        chdir($parentDirectory);

        if (is_dir($projectDirectory)) {
            chdir($projectDirectory);

            self::executeCommand("git remote get-url origin", $output, $status);

            if ($output[0] !== $repository) {
                self::executeCommand("git remote set-url origin $repository", $output, $status);
            }

            self::executeCommand("git remote update", $output, $status);
        } else {
            self::executeCommand("git clone $repository $projectDirectory -q", $output, $status);
            chdir($projectDirectory);
        }

        self::executeCommand("git checkout $commitId -q", $output, $status);

        chdir($currentDirectory);
    }

    private static function executeCommand($command, &$output, &$status): void
    {
        exec($command, $output, $status);
        if ($status) {
            throw new \RuntimeException("Running `$command` failed with exit code $status\n");
        }
    }
}
