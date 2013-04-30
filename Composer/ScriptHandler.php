<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Composer;

use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * A hack to work around the missing support for js assets in composer
 *
 * @see http://groups.google.com/group/composer-dev/browse_thread/thread/e9e2f7d919aadfec
 *
 * @author David Buchmann
 */
class ScriptHandler
{
    const CREATE_COMMIT_ID = '271e0114a039ab256ffcceacdf7f361803995e05';

    const CKEDITOR_COMMIT_ID = 'bba29309f93a1ace1e2e3a3bd086025975abbad0';

    public static function downloadCreateAndCkeditor($event)
    {
        ScriptHandler::downloadCreate($event);
        ScriptHandler::downloadCkeditor($event);
    }

    public static function downloadCreate($event)
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

    public static function downloadCkeditor($event)
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
     * @param string $directory The directory where the repository should be clone into
     * @param string $repository The git repository
     * @param string $commitId The commit id
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
            exec("git remote update", $output, $status);
            if ($status) {
                die("Running git pull $repository failed with $status\n");
            }
        } else {
            exec("git clone $repository $projectDirectory -q", $output, $status);
            if ($status) {
                die("Running git clone $repository failed with $status\n");
            }
            chdir($projectDirectory);
        }

        exec("git checkout $commitId -q", $output, $status);
        if ($status) {
            die("Running git clone $repository failed with $status\n");
        }

        chdir($currentDirectory);
    }
}
