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
    public static function initSubmodules($event)
    {
        $status = null;
        $output = array();
        $dir = getcwd();
        chdir(__DIR__.DIRECTORY_SEPARATOR.'..');
        exec('git submodule sync', $output, $status);
        if ($status) {
            chdir($dir);
            die("Running git submodule sync failed with $status\n");
        }
        exec('git submodule update --init --recursive', $output, $status);
        chdir($dir);
        if ($status) {
            die("Running git submodule --init --recursive failed with $status\n");
        }
    }

    public static function downloadCkeditor($event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();

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
            $commit = 'bba29309f93a1ace1e2e3a3bd086025975abbad0';
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
            exec("git clone $repository $projectDirectory", $output, $status);
            if ($status) {
                die("Running git clone $repository failed with $status\n");
            }
        }

        exec("git checkout $commitId", $output, $status);
        if ($status) {
            die("Running git clone $repository failed with $status\n");
        }

        chdir($currentDirectory);
    }
}
